const fs = require("fs");
const path = require("path");
const glob = 'public';

const pages = fs.readdirSync(glob).filter(f => f.endsWith('.php'));
pages.forEach(page => {
    let filepath = path.join(glob, page);
    let original = fs.readFileSync(filepath, 'utf8');
    let text = original;

    // Remove overlapping background colors on sections and structural divs, except for specific cards if needed
    text = text.replace(/\bbg-white\b/g, 'bg-white/10'); // Make white backgrounds translucent like cards
    text = text.replace(/\bbg-gray-50\b/g, 'bg-transparent');
    text = text.replace(/\bbg-himatep-dark\b/g, 'bg-transparent');

    // Change text colors to make them legible on dark blue background
    text = text.replace(/\btext-himatep-green\b/g, 'text-white');
    text = text.replace(/\btext-himatep-dark\b/g, 'text-white');
    text = text.replace(/\btext-gray-800\b/g, 'text-white');
    text = text.replace(/\btext-gray-700\b/g, 'text-gray-200');
    text = text.replace(/\btext-gray-600\b/g, 'text-gray-300');
    text = text.replace(/\btext-gray-500\b/g, 'text-gray-300');
    
    // For proker cards etc., adjust border colors that might be too dark
    text = text.replace(/\bborder-gray-400\b/g, 'border-white/20');
    text = text.replace(/\bborder-gray-200\b/g, 'border-white/10');

    if(original !== text) {
        fs.writeFileSync(filepath, text, 'utf8');
    }
});
console.log('Fixed PHP styles');
