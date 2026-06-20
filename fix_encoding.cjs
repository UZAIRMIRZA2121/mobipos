const fs = require('fs');
let c = fs.readFileSync('public/assets/js/script.js', 'utf8');
c = c.replace(/âœ“/g, '✓')
     .replace(/âš /g, '⚠')
     .replace(/âœ•/g, '✕')
     .replace(/â„¹/g, 'ℹ')
     .replace(/Ã—/g, '✕')
     .replace(/Ã¢â‚¬â€ /g, '—')
     .replace(/Ã°Å¸â€ /g, '🔍')
     .replace(/Ãƒâ€”/g, '×')
     .replace(/Ã‚Â·/g, '·')
     .replace(/âˆ’/g, '−')
     .replace(/Ã—/g, '✕')
     .replace(/Ã¢Å“â€œ/g, '✓')
     .replace(/Ã¢Å¡Â /g, '⚠')
     .replace(/Ã¢Å“â€¢/g, '✕')
     .replace(/Ã¢â€žÂ¹/g, 'ℹ')
     .replace(/Ãƒâ€”/g, '✕');
fs.writeFileSync('public/assets/js/script.js', c);
