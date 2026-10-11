import fs from 'node:fs';
import path from 'node:path';
import { parse, compileScript, compileTemplate } from '@vue/compiler-sfc';
const root=path.resolve('resources/js'); let count=0; const failures=[];
function walk(dir){ for(const entry of fs.readdirSync(dir,{withFileTypes:true})) { const p=path.join(dir,entry.name); if(entry.isDirectory()) walk(p); else if(p.endsWith('.vue')){
  count++; const source=fs.readFileSync(p,'utf8'); const {descriptor,errors}=parse(source,{filename:p});
  failures.push(...errors.map(x=>`${p}: ${x.message??x}`));
  try { if(descriptor.script||descriptor.scriptSetup) compileScript(descriptor,{id:p}); } catch(e){failures.push(`${p}: ${e.message}`);}
  if(descriptor.template){const result=compileTemplate({source:descriptor.template.content,filename:p,id:p});failures.push(...result.errors.map(x=>`${p}: ${x.message??x}`));}
 } } }
walk(root); console.log(`${count} Vue components parsed and compiled; ${failures.length} errors.`);
fs.mkdirSync('qa/results',{recursive:true});
fs.writeFileSync('qa/results/frontend.json',JSON.stringify({executedAt:new Date().toISOString(),components:count,failures},null,2)+'\n');
for(const failure of failures) console.error(failure); process.exitCode=failures.length?1:0;
