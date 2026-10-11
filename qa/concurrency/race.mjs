// Execute real parallel HTTP requests against a prepared disposable MySQL staging server.
// No login credentials are embedded. Sessions/CSRF values are supplied by environment names in the plan.
import fs from 'node:fs';
import assert from 'node:assert/strict';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
assert.equal(process.env.QA_TARGET,'disposable','Set QA_TARGET=disposable for synthetic staging only.');
assert.equal(process.env.QA_ALLOW_MUTATIONS,'true','Races change stock/payment state; QA_ALLOW_MUTATIONS=true required.');
const planFile=process.argv[2]; assert(planFile,'Supply a prepared race plan JSON; see example.json.');
const plan=JSON.parse(fs.readFileSync(planFile,'utf8')); const url=new URL(plan.baseURL);
assert(['http:','https:'].includes(url.protocol));
assert(['127.0.0.1','localhost'].includes(url.hostname)||process.env.QA_ALLOW_REMOTE==='true','Remote staging needs QA_ALLOW_REMOTE=true.');
assert(!url.username&&!url.password,'Credentials in baseURL are forbidden.');
const baseOrigin=url.origin;
function headers(actor){assert(actor?.cookieEnv&&process.env[actor.cookieEnv],'Missing isolated authenticated cookie environment variable.');assert(actor?.csrfEnv&&process.env[actor.csrfEnv],'Missing CSRF token variable.');return {'Cookie':process.env[actor.cookieEnv],'X-XSRF-TOKEN':process.env[actor.csrfEnv],'Accept':'application/json','Content-Type':'application/json'};}
function resolve(relative){const u=new URL(relative,plan.baseURL);assert.equal(u.origin,baseOrigin,'Cross-origin race request refused.');return u;}
async function observe(check){const h=headers(check.actor);const r=await fetch(resolve(check.path),{headers:h,redirect:'manual',signal:AbortSignal.timeout(15000)});assert.equal(r.status,200,'Observation must be authorized Inertia200.');const body=await r.text();let payload;
 if((r.headers.get('content-type')??'').includes('application/json')) payload=JSON.parse(body);
 else {const attr=body.match(/data-page="([^"]+)"/);assert(attr,'Missing Inertia page payload.');payload=JSON.parse(attr[1].replaceAll('&quot;','"').replaceAll('&#039;',"'").replaceAll('&lt;','<').replaceAll('&gt;','>').replaceAll('&amp;','&'));}
 let value=payload;for(const part of check.valuePath.split('.')) {assert(value!=null,`Missing observation path ${check.valuePath}`);value=value[part];}if(check.find){assert(Array.isArray(value));value=value.find(row=>String(row[check.find.field])===String(check.find.value));assert(value,'Fixture row not in returned page; adjust search to select the exact record.');value=value[check.find.read];}return value;}
assert(Array.isArray(plan.requests)&&plan.requests.length>=2,'At least two simultaneous requests required.');
assert(Array.isArray(plan.observations)&&plan.observations.length,'State assertions are mandatory; HTTP302 alone is not business success.');
for(const check of plan.observations) assert.deepEqual(await observe(check),check.before,`Before-state mismatch: ${check.label}`);
const responses=await Promise.all(plan.requests.map(async(req)=>{assert(['POST','PUT','PATCH','DELETE'].includes(req.method));const r=await fetch(resolve(req.path),{method:req.method,headers:headers(req.actor),body:JSON.stringify(req.body??{}),redirect:'manual',signal:AbortSignal.timeout(15000)});assert(req.allowedStatuses.includes(r.status),`Unexpected status for ${req.label}: ${r.status}`);return {label:req.label,status:r.status};}));
const state=[];for(const check of plan.observations){const actual=await observe(check);assert.deepEqual(actual,check.after,`Concurrent invariant failed: ${check.label}`);state.push({label:check.label,actual});}
const result={scenario:plan.scenario,executedAt:new Date().toISOString(),requests:responses,state};
fs.mkdirSync(path.join(root,'qa/results'),{recursive:true});fs.writeFileSync(path.join(root,'qa/results/concurrency.json'),JSON.stringify(result,null,2)+'\n');
console.log('PASS '+plan.scenario+' — parallel requests and final state matched. Verify movement counts/uniqueness with reconciliation.mysql.sql too.');
