import fs from 'node:fs';
import path from 'node:path';
import { parse, compileScript, compileTemplate } from '@vue/compiler-sfc';
const files = fs.readdirSync('resources/js', { recursive: true }).filter(p => p.endsWith('.vue'));
let errors = [];
for (const file of files) {
 const filename = path.join('resources/js', file);
 const { descriptor, errors: parseErrors } = parse(fs.readFileSync(filename, 'utf8'), { filename });
 errors.push(...parseErrors.map(e => `${file}: ${e}`));
 const script = descriptor.scriptSetup ? compileScript(descriptor, { id: file }) : null;
 if (descriptor.template) {
  const template = compileTemplate({ source: descriptor.template.content, filename, id: file, compilerOptions: { bindingMetadata: script?.bindings } });
  errors.push(...template.errors.map(e => `${file}: ${e}`));
  for (const match of template.code.matchAll(/_resolveComponent\("([A-Z][^"]*)"\)/g)) {
   errors.push(`${file}: unresolved component ${match[1]}`);
  }
 }
}
if (errors.length) { console.error(errors.join('\n')); process.exit(1); }
console.log(`Compiled and checked component imports for all ${files.length} Vue components.`);
