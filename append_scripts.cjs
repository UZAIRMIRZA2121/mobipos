const fs = require('fs');
const path = require('path');

const mappings = [
    { file: 'shop/dashboard.blade.php', scripts: ['dashboard.js', 'sales.js'] },
    { file: 'pos/index.blade.php', scripts: ['pos.js', 'customers.js', 'products.js', 'sales.js', 'categories.js'] },
    { file: 'pos/billing.blade.php', scripts: ['pos.js', 'customers.js', 'products.js', 'sales.js'] },
    { file: 'installments/index.blade.php', scripts: ['customers.js', 'products.js', 'sales.js'] },
    { file: 'shop/purchase_orders.blade.php', scripts: ['purchases.js', 'products.js'] },
    { file: 'shop/expenses.blade.php', scripts: ['expenses.js'] },
    { file: 'shop/reports.blade.php', scripts: ['dashboard.js'] }, // uses generateReport
];

for (const map of mappings) {
    const fullPath = path.join(__dirname, 'resources', 'views', map.file);
    if (fs.existsSync(fullPath)) {
        let content = fs.readFileSync(fullPath, 'utf8');
        if (!content.includes("@section('scripts')")) {
            let scriptTags = map.scripts.map(s => `<script src="{{ asset('assets/js/${s}') }}?v={{ time() }}"></script>`).join('\n');
            const section = `\n@section('scripts')\n${scriptTags}\n@endsection\n`;
            
            // if ends with @endsection (like from layout content), insert before it if we must? No, @section('scripts') is separate from @section('content').
            content += section;
            fs.writeFileSync(fullPath, content);
            console.log(`Updated ${map.file}`);
        }
    }
}
