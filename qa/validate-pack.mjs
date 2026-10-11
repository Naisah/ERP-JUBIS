import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
const root=path.resolve('qa');
const routes=JSON.parse(fs.readFileSync(path.join(root,'data/routes.json')));
const matrix=JSON.parse(fs.readFileSync(path.join(root,'data/endpoint-matrix.json')));
const expected=routes.flatMap(r=>r.methods.map(m=>m+' '+r.uri));
assert.equal(new Set(expected).size,expected.length);
assert.deepEqual(matrix.map(r=>r.method+' '+r.uri),expected,'Matrix must cover each method exactly once.');
for(const r of matrix) for(const key of ['id','method','uri','roles','schema','happy_assertion','schema_failure','negative','auth','rate','timeout']) assert(r[key],`Missing ${key} for ${r.uri}`);
const collection=JSON.parse(fs.readFileSync(path.join(root,'api/jubis.postman_collection.json')));
assert(collection.info.schema.endsWith('/v2.1.0/collection.json'));
let requests=0;for(const folder of collection.item)for(const item of folder.item){requests++;assert(['GET','POST','PUT','PATCH','DELETE'].includes(item.request.method));assert(item.request.url.startsWith('{{base_url}}/'));assert.equal(item.protocolProfileBehavior.followRedirects,false);assert(!item.event[0].script.exec.join('\n').includes('NaN'));if(item.request.body) JSON.parse(item.request.body.raw);}
assert(requests>=matrix.filter(r=>r.handler_exists).length);
const required=['README.md','TEST_STRATEGY.md','FUNCTIONAL_SCENARIOS.md','RELEASE_CHECKLIST.md','ENDPOINT_MATRIX.md','ENDPOINT_MATRIX.csv'];
for(const file of required){const body=fs.readFileSync(path.join(root,file),'utf8');for(const match of body.matchAll(/\]\(([^)]+)\)/g)){const target=match[1];if(/^(https?:|#)/.test(target)) continue;assert(fs.existsSync(path.resolve(root,target.split('#')[0])),`Broken doc link ${file}: ${target}`);}}
const race=JSON.parse(fs.readFileSync(path.join(root,'concurrency/example.json')));assert.equal(race.requests.length,2);assert(race.observations.length>0);
console.log(`Validated ${matrix.length} endpoint contracts, ${requests} Postman requests, guarded race example and documentation links.`);
