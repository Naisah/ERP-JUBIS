<?php
// Read only saved QA evidence. This script never bootstraps Laravel or accesses a DB.
$root=__DIR__;
date_default_timezone_set('Asia/Manila');
function suiteEvidence(string $file): array {
    if(!is_file($file)) return ['tests'=>0,'assertions'=>0,'failures'=>0,'errors'=>0,'skipped'=>0,'cases'=>[],'missing'=>true];
    $xml=simplexml_load_file($file); if(!$xml) throw new RuntimeException('Invalid JUnit XML: '.$file);
    $cases=$xml->xpath('//testcase'); $result=['tests'=>count($cases),'assertions'=>0,'failures'=>0,'errors'=>0,'skipped'=>0,'cases'=>$cases,'missing'=>false];
    foreach($cases as $case) { $result['assertions']+=(int)$case['assertions'];foreach(['failure'=>'failures','error'=>'errors','skipped'=>'skipped'] as $tag=>$key) if(isset($case->$tag)) $result[$key]++; }
    return $result;
}
$baseline=suiteEvidence($root.'/results/baseline.xml');$gates=suiteEvidence($root.'/results/release-gates.xml');
$metadata=[
 'SEC01'=>['P0','Unsigned payment webhook posts an invoice as paid','app/Http/Controllers/WebhookController.php','Signature check is conditional on both header and secret; authentication must fail closed.'],
 'SEC02'=>['P0','Guest can process simulator payment','routes/web.php; WebhookController::processPayment','No auth middleware/owner check; synthetic guest changed an unpaid invoice to paid. Production mock endpoints must be unavailable.'],
 'SEC03'=>['P0','Another client can process someone else’s simulator payment','app/Http/Controllers/WebhookController.php','Authenticated unrelated client changed another invoice to paid.'],
 'SEC04'=>['P0','Hour-old signed payment event is accepted','app/Http/Controllers/WebhookController.php','Valid HMAC alone does not prevent replay; no signature timestamp freshness/dedup validation.'],
 'FIN01'=>['P0','Paid invoice reopens as Net30 credit','routes/web.php: accept-terms','Paid invoice with1000 units becomes on_terms; no status-transition guard.'],
 'FIN02'=>['P0','Manual payment exceeds invoice balance','app/Http/Controllers/Admin/InvoiceController.php','225 is accepted for invoice224; expected422/no mutation.'],
 'FIN03'=>['P1','Zero manual payment is accepted','app/Http/Controllers/Admin/InvoiceController.php','min:0 accepts zero; payment must be positive and state-safe.'],
 'FIN04'=>['P1','Approval drops PayMongo link reference','app/Http/Controllers/Admin/QuoteController.php','Controller reads link.id but service returns reference_id; invoice reference stays null.'],
 'FIN05'=>['P1','Generated simulation checkout URL has no route','app/Services/PayMongoService.php','Service generates /api/mock/payment/link_…; only /api/mock/payment/invoice/{id} is registered.'],
 'FIN06'=>['P1','Centavo conversion truncates0.29 to28','app/Services/PayMongoService.php','intval(total_amount*100) truncates binary float instead of exact centavos.'],
 'EXP01'=>['P1','Invoice PDF uses wrong line-price fields','resources/views/pdf/invoice.blade.php','Template reads unit_price/total_price but quote_items stores quoted_price; priced lines render0.'],
 'EXP02'=>['P1','Superadmin cannot download client invoice','routes/web.php: invoices.pdf','Policy allows owner or role admin only; super_admin is403. Finance export policy also needs business agreement.'],
 'EXP03'=>['P1','CSV cells with commas shift columns','app/Http/Controllers/Admin/ProductController.php','SKU/brand not CSV-quoted; combined gate also requires formula prefix neutralization. Column assertion fails first; formula execution was not exercised in Excel.'],
 'INV01'=>['P0','Reject/approve/reject manufactures stock','app/Http/Controllers/Admin/QuoteController.php','Stock opens100, reserves2, restores twice and ends102; status transitions do not reserve again or block cycling.'],
 'INV02'=>['P1','Manual stock edit has no movement audit','app/Http/Controllers/Admin/ProductController.php','Stock adjustment commits without signed delta/actor/reference movement.'],
 'INV03'=>['P0','Received PO reversal can make stock negative','app/Http/Controllers/Admin/PurchaseOrderController.php','Remaining stock2 minus reversal5 becomes negative; no sufficiency/locking guard.'],
 'INT01'=>['P2','Repeated delivered status sends duplicate email','app/Http/Controllers/Admin/ShipmentController.php','Same delivered transition causes two fake mail sends; no old-status/idempotency guard.'],
 'INT02'=>['P1','Tracking page fails to render shipment','routes/web.php; resources/views/mock-tracking.blade.php','Route passes shipments while template expects shipment; known tracking returns500.'],
 'CFG01'=>['P1','Settings accepts unknown secret and privilege fields','routes/web.php; HandleInertiaRequests::share','Unvalidated request.all is cached; shared settings can expose those values on public pages. Regression proves storage; public-sharing risk is confirmed by source inspection.'],
 'CFG02'=>['P1','Negative VAT setting is accepted','routes/web.php: admin/settings','No schema validation; vat_rate=-1 succeeds302 instead of422.'],
 'CAT01'=>['P1','Inactive product can be added by direct request','app/Http/Controllers/QuoteController.php','Catalog visibility is not enforced in cart.add; one quote line is created for inactive product.'],
 'SCHEMA01'=>['P0','Database permits duplicate invoices for a quote','database/migrations/2026_09_27_135033_create_extended_erp_tables.php','Second invoice insert succeeds; no quote_id uniqueness. This proves missing safeguard, not an executed MySQL approval race.'],
 'SCHEMA02'=>['P1','Database permits duplicate shipments for an invoice','database/migrations/2026_09_27_135033_create_extended_erp_tables.php','Second direct insert succeeds; validation uniqueness alone is not concurrency protection.'],
 'RMA01'=>['P1','RMA line failure leaves an incomplete parent','app/Http/Controllers/QuoteController.php: requestRMA','Injected item insert failure returns500 and leaves r_m_a_s parent committed; no transaction.'],
 'API01'=>['P2','Registered resource action has no controller handler','routes/web.php; Admin/ProductController.php; Admin/QuoteController.php','Inventory show and quote create/store/edit/destroy are registered but methods are absent. Remove unsupported routes or implement documented contracts.'],
];
$defects=[];$handlerIndex=0;
foreach($gates['cases'] as $case){
    if(!isset($case->failure)&&!isset($case->error)) continue;
    $name=(string)$case['name'];preg_match('/test_([a-z]+[0-9]{2})_/',$name,$match);$id=strtoupper($match[1]??'UNMAPPED');
    [$priority,$title,$component,$details]=$metadata[$id]??['P1','Unmapped failing release gate','qa/tests',$name];
    $display=$id==='API01'?$id.'-'.(++$handlerIndex):$id;
    $failure=isset($case->failure)?$case->failure:$case->error;
    $first=explode("\n",trim((string)$failure))[0];$first=mb_substr($first,0,250);
    $defects[]=compact('display','id','priority','title','component','details','name','first');
}
usort($defects,fn($a,$b)=>strcmp($a['priority'],$b['priority'])?:strcmp($a['display'],$b['display']));
$counts=['P0'=>0,'P1'=>0,'P2'=>0,'P3'=>0];foreach($defects as $d)$counts[$d['priority']]++;
$findings="# Reproduced release-gate findings\n\nGenerated from saved release-gates JUnit evidence on ".date('c').". Application code is unchanged. Each row is a failing gate instance; related cases may describe one root cause. These are isolated synthetic reproductions, not production exploits or live transactions.\n\n";
$findings.="Open gate instances: ".implode(', ',array_map(fn($p)=>$p.'='.$counts[$p],array_keys($counts))).". All require triage and retest; severity is provisional engineering impact.\n\n";
$findings.="Reproduce from repo root: `php vendor/bin/phpunit -c qa/phpunit.xml --group release-gates --filter test_<lowercase ID>`. Full output/JUnit are under ignored qa/results/. Gate assertions intentionally remain strict rather than marking defects skipped.\n\n";
foreach($defects as $d){$findings.="## {$d['display']} {$d['priority']} — {$d['title']}\n\n{$d['details']}\n\nComponent: `{$d['component']}`. Reproduction: `{$d['name']}` in `qa/tests/ReleaseGateTest.php`. Observed assertion: ".str_replace(["\r","\n"],['',' '],$d['first'])."\n\n";}
$findings.="## Additional source-inspected risks, not yet runtime-verified release results\n\n";
$findings.="- P1: reports calculate open AR from unpaid only, excluding partial/on_terms balances; MySQL reconciliation required.\n- P1: invoice PDF shipping is added to displayed total without a corresponding invoice/payment ledger change; verify with an actual shipped invoice.\n- P1: live Lalamove path appears to request a quotation then fabricate booking/tracking details with hardcoded coordinates; no live/sandbox booking evidence.\n- P1: low-stock automatic PO event uses total_amount/unit_price/total_price versus schema total_cost/unit_cost; exercise with matching supplier to validate failure/rollback.\n- P1: RMA duplicate query has no DB uniqueness; concurrent request not executed.\n- P1: settings are cache-only and VAT calculations remain hardcoded12%; validate durability/config behavior before release.\n- P1: production HTTPS/session/reset/audit/backup controls have not been verified.\n- P2: stock scan checks variants only and mails a hardcoded address; standalone/custom threshold/recipient and dedup policy unresolved.\n\nThese items must be reproduced on disposable staging and assigned evidence; do not count them as additional executed failures. See TEST_STRATEGY.md for absent GL/XLSX/RMA approval scope.\n";
file_put_contents($root.'/KNOWN_FINDINGS.md',$findings);
$uiFile=$root.'/results/ui/report.json';$ui=is_file($uiFile)?json_decode(file_get_contents($uiFile),true):null;
$uiPass=$ui?count(array_filter($ui['results'],fn($r)=>$r['status']==='pass')):0;$uiTotal=$ui?count($ui['results']):0;
$uiWhen=$ui?(new DateTimeImmutable($ui['executedAt']))->setTimezone(new DateTimeZone('Asia/Manila'))->format(DATE_ATOM):'';
$roleCases=count(array_filter($baseline['cases'],fn($c)=>str_starts_with((string)$c['name'],'test_every_role_boundary')));
$actualPass=$baseline['tests']-$baseline['failures']-$baseline['errors']-$baseline['skipped'];
$routeRows=json_decode(file_get_contents($root.'/data/routes.json'),true);$matrix=json_decode(file_get_contents($root.'/data/endpoint-matrix.json'),true);
$report="# QA execution report\n\nEvidence snapshot generated ".date('c').". This is a test-plan delivery and an isolated local execution, not production sign-off. Results below come from saved artifacts; rerun report.php after fresh test runs. The candidate commit and deployment metadata must still be attached at release.\n\n";
$report.="| Check | Actual outcome | Evidence / scope |\n|---|---|---|\n";
$report.="| Baseline PHPUnit | {$actualPass}/{$baseline['tests']} pass; {$baseline['assertions']} assertions; {$baseline['failures']} failures; {$baseline['errors']} errors | qa/results/baseline.xml; {$roleCases} middleware role cases and ".($baseline['tests']-$roleCases)." other HTTP/workflow/integration/validation cases |\n";
$report.="| Required release gates | {$gates['tests']} tests; {$gates['failures']} failures; {$gates['errors']} errors; {$gates['skipped']} skipped | qa/results/release-gates.xml; known defects remain failing |\n";
$report.="| Browser checks | ".($ui?"$uiPass/$uiTotal pass; executed {$uiWhen}":'NOT RUN — no report saved')." | qa/results/ui/report.json; Chromium, isolated SQLite QA server, blocked external services |\n";
$report.="| Route/matrix inventory | ".count($routeRows)." routes; ".count($matrix)." method/endpoint rows | Includes PUT/PATCH independently; inventory is planned coverage, not all endpoint HTTP execution |\n";
$frontendFile=$root.'/results/frontend.json';
if(is_file($frontendFile)) { $frontend=json_decode(file_get_contents($frontendFile),true);$report.="| Vue static compilation | {$frontend['components']} components; ".count($frontend['failures'])." errors | qa/results/frontend.json; syntax/template checks only, not browser correctness |\n"; }
$report.="\nRelease status: **BLOCKED** while mandatory gate failures or unexecuted staging criteria remain. Do not substitute baseline-only success for release-gate success.\n\n";
$report.="## What the automated results demonstrate\n\nReal migrations/validations and in-process Laravel HTTP workflow actions execute against SQLite :memory: with synthetic fixtures. Successful checks include quote/VAT/stock reserve -> invoice -> partial/full manual payments; rollback of multi-item insufficient stock and injected PO item failure; sequential reject/receipt/RMA/shipment handling; owner-denied cart/PDF/terms; input/schema negatives; registration/profile escalation protection; login throttling/logout/CSRF; mocked payment transport and signed callback; mail escaping and variant low-stock notification. Middleware role cases are not end-to-end requests to every controller.\n\n";
if($ui){$report.="Browser scope: public pages, filter/sort persistence, guest add-to-quote redirect, synthetic client cart submission, forbidden client admin page, six staff logins/selected allowed pages and JavaScript errors.\n\n";foreach($ui['results'] as $result)if($result['status']!=='pass')$report.="- Browser failure {$result['id']}: ".str_replace("\n",' ',mb_substr($result['message']??'',0,250))."\n";}
$report.="\n## Not executed / remaining release evidence\n\n- MySQL8 row-lock races, decimal strict-mode/storage boundary/FK behavior and report reconciliation. SQLite is not equivalent.\n- Live/sandbox PayMongo or Lalamove end-to-end provider checks, real Gmail/SMTP transport and production map/geocoding behavior. Local integrations are faked/simulated.\n- Actual multipage PDF extraction/rendering and Excel execution/formula safety; gate checks template/CSV parser behavior only. XLSX/GL/RMA approval features are absent.\n- Full81 method/endpoint happy/negative/controller contracts; Postman collection was generated/validated, not sent wholesale.\n- Cross-browser/mobile/accessibility, performance/load/soak/rate policy, production TLS/cookies/token expiry, full audit/backup restore/deployment rollback and dependency vulnerability triage.\n\n## Safety and reproducibility\n\nOnly qa/ files are added. No production app routes/controllers, real inventory/orders, normal port8000 server or tunnel configuration were edited. CLI QA forces in-memory SQLite; UI QA guards qa/results/ui.sqlite and separate sessions. No real mail/payment/courier call was made. Scripts return nonzero on failures; evidence files are ignored by Git. README.md provides all execution/preparation commands.\n";
file_put_contents($root.'/EXECUTION_REPORT.md',$report);
echo "Saved execution report and ".count($defects)." reproduced failing gate instances.\n";
