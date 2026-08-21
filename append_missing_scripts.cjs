const fs = require('fs');
const path = require('path');

const mappings = [
    { file: 'products/index.blade.php', scripts: ['products.js', 'categories.js', 'customers.js'] },
    { file: 'categories/index.blade.php', scripts: ['categories.js'] },
    { file: 'customers/index.blade.php', scripts: ['customers.js'] },
    { file: 'sales/index.blade.php', scripts: ['sales.js'] },
    { file: 'sales/invoices.blade.php', scripts: ['sales.js'] }
];

for (const map of mappings) {
    const fullPath = path.join(__dirname, 'resources', 'views', map.file);
    if (fs.existsSync(fullPath)) {
        let content = fs.readFileSync(fullPath, 'utf8');
        if (!content.includes("@section('scripts')")) {
            let scriptTags = map.scripts.map(s => `<script src="{{ asset('assets/js/${s}') }}?v={{ time() }}"></script>`).join('\n');
            const section = `\n@section('scripts')\n${scriptTags}\n@endsection\n`;
            
            content += section;
            fs.writeFileSync(fullPath, content);
            console.log(`Updated ${map.file}`);
        }
    } else {
        console.log(`File not found: ${map.file}`);
    }
}
