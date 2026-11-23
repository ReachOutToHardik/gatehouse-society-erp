// Prints an HTML file to PDF with headless Edge (DevTools Page.printToPDF)
import { spawn } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const [, , input, output, profile] = process.argv;
const EDGE = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const edge = spawn(EDGE, ['--headless=new', '--remote-debugging-port=9334', `--user-data-dir=${profile}`, '--no-first-run', '--disable-gpu', 'about:blank'], { stdio: 'ignore' });
const sleep = ms => new Promise(r => setTimeout(r, ms));
let targets;
for (let i = 0; i < 40; i++) {
  try { targets = await (await fetch('http://127.0.0.1:9334/json')).json(); if (targets.find(t => t.type === 'page')) break; } catch {}
  await sleep(250);
}
const ws = new WebSocket(targets.find(t => t.type === 'page').webSocketDebuggerUrl);
await new Promise(r => ws.addEventListener('open', r, { once: true }));
let id = 0; const pending = new Map(); let onLoad;
ws.addEventListener('message', ev => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) { pending.get(m.id)(m); pending.delete(m.id); }
  if (m.method === 'Page.loadEventFired' && onLoad) onLoad();
});
const send = (method, params = {}) => new Promise(r => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
await send('Page.enable');
const loaded = new Promise(r => (onLoad = r));
await send('Page.navigate', { url: pathToFileURL(input).href });
await loaded; await sleep(800);
const r = await send('Page.printToPDF', { printBackground: true, preferCSSPageSize: true, displayHeaderFooter: false });
if (!r.result) { console.error(JSON.stringify(r.error)); process.exit(1); }
writeFileSync(output, Buffer.from(r.result.data, 'base64'));
console.log('pdf written');
ws.close(); edge.kill(); process.exit(0);
