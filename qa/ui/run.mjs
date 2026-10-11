import fs from 'node:fs';
import path from 'node:path';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
const localRequire=createRequire(path.join(process.env.QA_NODE_MODULES??path.join(root,'qa','node_modules'),'__qa_loader.cjs'));
const {chromium}=localRequire('playwright');
const base='http://127.0.0.1:9001';
const health=await fetch(base+'/up'); assert.equal(health.status,200,'Start the isolated QA server on port9001.');
assert.equal(health.headers.get('X-Jubis-QA-Environment'),'isolated-ui','Wrong server on port9001; refusing browser mutations.');
// This runner has a fixed loopback target backed by qa/ui/server.php. Never point it at the tunnel.
const marker=await fetch(base+'/products').then(r=>r.text());
assert(marker.includes('QA-UI-'),'QA fixture marker missing; refusing browser mutations.');
const browser=await chromium.launch({headless:true,...(process.env.QA_BROWSER_PATH?{executablePath:process.env.QA_BROWSER_PATH}:{})});
process.once('SIGINT',async()=>{await browser.close();process.exit(130);});
const out=path.join(root,'qa/results/ui'); fs.mkdirSync(out,{recursive:true});
const results=[]; const errors=[]; let context=await browser.newContext({viewport:{width:1440,height:900}}); let page=await context.newPage();
await context.route('**/*',route=>{ const url=new URL(route.request().url()); return ['127.0.0.1','localhost'].includes(url.hostname)?route.continue():route.abort(); });
page.on('pageerror',e=>errors.push(e.message));
async function check(id,fn){page.setDefaultTimeout(7000);page.setDefaultNavigationTimeout(10000);const start=Date.now();try{await fn();results.push({id,status:'pass',ms:Date.now()-start});console.log('PASS '+id);}catch(e){results.push({id,status:'fail',message:e.message,ms:Date.now()-start});console.log('FAIL '+id+' '+e.message.split('\n')[0]);await page.screenshot({path:path.join(out,id+'.png'),fullPage:true});}}
async function login(role){await page.goto(base+'/login');await page.locator('#email').fill(role+'-ui@example.test');await page.locator('#password').fill('QaTestOnly123!');await page.locator('button[type=submit]').click();await page.waitForURL(role==='client'?/\/dashboard$/:/\/admin\/dashboard$/);}
for(const [id,url,title] of [['UI01','/products','Product Catalog'],['UI02','/about','About'],['UI03','/contact','Contact'],['UI04','/login','Log In'],['UI05','/register','Create an Account']]) await check(id,async()=>{const r=await page.goto(base+url);assert.equal(r.status(),200);await page.waitForSelector('#app');assert((await page.locator('body').innerText()).toLowerCase().includes(title.toLowerCase()),'Visible page content missing');});
await check('UI06',async()=>{await page.goto(base+'/products');await page.getByPlaceholder('Search by product name, brand, or SKU...').fill('Alpha');await page.waitForURL(/search=Alpha/);await page.getByRole('heading',{name:'Product Catalog'}).waitFor();assert((await page.locator('body').innerText()).includes('QA Alpha Cable'));assert(!(await page.locator('body').innerText()).includes('QA Beta Switch'));await page.locator('select').selectOption('name');await page.waitForURL(/sort=name/);assert(new URL(page.url()).searchParams.get('search')==='Alpha');await page.reload();assert.equal(await page.locator('select').inputValue(),'name');});
await check('UI07',async()=>{await page.goto(base+'/products');await page.getByRole('button',{name:'Add to Quote',exact:true}).first().click();await page.waitForURL(/\/login$/);});
await check('UI08',async()=>{await login('client');const before=((await page.locator('body').innerText()).match(/#QTE-\d+/g)??[]).length;await page.goto(base+'/products?search=QA-UI-1');const added=page.waitForResponse(r=>r.request().method()==='POST'&&r.url().endsWith('/cart/add'));await page.getByRole('button',{name:'Add to Quote',exact:true}).first().click();await added;await page.goto(base+'/cart');assert((await page.locator('body').innerText()).includes('QA Alpha Cable'));await page.getByPlaceholder('Enter delivery location...').fill('QA Test Address');page.once('dialog',d=>d.accept());await page.getByRole('button',{name:'Submit Quote Request'}).click();await page.waitForURL(/\/products$/);await page.goto(base+'/dashboard');const text=await page.locator('body').innerText();assert.equal((text.match(/#QTE-\d+/g)??[]).length,before+1);assert(text.includes('PENDING'));assert(text.includes('112.00'));});
await check('UI09',async()=>{const r=await page.goto(base+'/admin/settings');assert.equal(r.status(),403);});
await context.close();
for(const role of ['super_admin','admin','sales','finance','purchasing','warehouse']){
 context=await browser.newContext();page=await context.newPage();await context.route('**/*',route=>new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());
 page.on('pageerror',e=>errors.push(e.message));
 await check('UI10-'+role,async()=>{await login(role);await page.getByRole('heading').first().waitFor();assert(!(await page.locator('body').innerText()).includes('Internal Server Error'));const allowed=role==='sales'?'/admin/quotes':role==='finance'?'/admin/invoices':role==='warehouse'?'/admin/inventory':role==='purchasing'?'/admin/purchase-orders':'/admin/settings';const r=await page.goto(base+allowed);assert.equal(r.status(),200);});
 await context.close();
}
await browser.close();results.push({id:'UI11-JavaScript',status:errors.length?'fail':'pass',message:errors.join('; ')});
fs.writeFileSync(path.join(out,'report.json'),JSON.stringify({base,executedAt:new Date().toISOString(),results},null,2)+'\n');
for(const r of results) console.log(`${r.status.toUpperCase()} ${r.id}${r.message?' '+r.message:''}`);
console.log(`${results.filter(x=>x.status==='pass').length}/${results.length} browser checks passed; report: qa/results/ui/report.json`);
process.exitCode=results.some(x=>x.status==='fail')?1:0;
