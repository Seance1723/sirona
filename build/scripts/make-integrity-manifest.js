#!/usr/bin/env node
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const root = path.resolve(__dirname, '../../theme');

// Build whitelist of critical files relative to theme root.
const whitelist = [
'inc/licensing/class-fx-license.php',
'inc/builders/header-footer/admin.php',
'inc/builders/header-footer/render.php',
'inc/builders/header-footer/storage.php',
'inc/mega-menu/walker.php',
'inc/woo/setup.php',
];

// Include all pro block renderers.
const blocksProDir = path.join(root, 'inc/blocks-pro');
if (fs.existsSync(blocksProDir)) {
fs.readdirSync(blocksProDir, { withFileTypes: true })
.filter((d) => d.isDirectory())
.forEach((d) => {
const renderPath = path.join('inc/blocks-pro', d.name, 'render.php');
if (fs.existsSync(path.join(root, renderPath))) {
whitelist.push(renderPath);
}
});
}

function sha256(file) {
return crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
}

const files = {};
for (const rel of whitelist) {
const abs = path.join(root, rel);
files[rel] = sha256(abs);
}

const pkg = require('../../package.json');
const payload = {
version: pkg.version,
generated_at: new Date().toISOString(),
files,
};

const secret = process.env.FX_RELEASE_SECRET || '';
const sig = crypto
.createHmac('sha256', secret)
.update(JSON.stringify(payload))
.digest('hex');

const manifest = { ...payload, sig };

function toPhp(value) {
if (Array.isArray(value)) {
return 'array(' + value.map((v) => toPhp(v)).join(', ') + ')';
}
if (value && typeof value === 'object') {
return (
'array(' +
Object.entries(value)
.map(([k, v]) => `'${k}' => ` + toPhp(v))
.join(', ') +
')'
);
}
return `'${value}'`;
}

const php = '<?php\nreturn ' + toPhp(manifest) + ';\n';
const outFile = path.join(root, 'inc/integrity/manifest.php');
fs.writeFileSync(outFile, php);
console.log('Manifest written to', outFile);