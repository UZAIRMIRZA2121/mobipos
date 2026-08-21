const fs = require('fs');
const path = require('path');

const scriptPath = path.join(__dirname, 'public', 'assets', 'js', 'script.js');
const outDir = path.join(__dirname, 'public', 'assets', 'js');

const lines = fs.readFileSync(scriptPath, 'utf8').split('\n');

const modules = {
    'core.js': [{start: 1, end: 380}, {start: 2570, end: 2612}, {start: 3300, end: 3333}],
    'dashboard.js': [{start: 381, end: 522}],
    'pos.js': [{start: 523, end: 1221}],
    'sales.js': [{start: 1222, end: 1671}],
    'products.js': [{start: 1672, end: 1989}, {start: 3334, end: lines.length}],
    'categories.js': [{start: 1990, end: 2081}],
    'purchases.js': [{start: 2082, end: 2167}, {start: 2714, end: 3242}],
    'customers.js': [{start: 2168, end: 2569}, {start: 3243, end: 3299}],
    'expenses.js': [{start: 2613, end: 2713}]
};

for (const [filename, ranges] of Object.entries(modules)) {
    let content = '';
    for (const range of ranges) {
        // lines array is 0-indexed, line numbers are 1-indexed
        content += lines.slice(range.start - 1, range.end).join('\n') + '\n';
    }
    fs.writeFileSync(path.join(outDir, filename), content);
    console.log(`Created ${filename} (${content.split('\n').length} lines)`);
}

// Rename script.js to script.js.bak
fs.renameSync(scriptPath, scriptPath + '.bak');
console.log('Backed up script.js to script.js.bak');
