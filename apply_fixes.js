const fs = require("fs");
const path = require("path");

function walkDir(dir) {
    let results = [];
    if (!fs.existsSync(dir)) return results;
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) { 
            results = results.concat(walkDir(file));
        } else if (file.endsWith(".php")) { 
            results.push(file);
        }
    });
    return results;
}

const phpFiles = walkDir("public");

phpFiles.forEach(file => {
    let original = fs.readFileSync(file, "utf8");
    let text = original;

    // Process <section> tags: change bg-white or bg-gray-50 to bg-batik-blue
    text = text.replace(/(<section[^>]*?class="[^"]*?)(?:\bbg-white\b|\bbg-gray-50\b)([^"]*?")/g, "$1bg-batik-blue$2");

    // Process index.php hero section specifically
    if (file.endsWith("index.php")) {
        text = text.replace(/bg-himatep-light hero-diagonal/g, "bg-batik-blue");
        text = text.replace(/text-himatep-dark mb-2 tracking-tight hero-text/g, "text-white mb-2 tracking-tight hero-text");
        text = text.replace(/text-gray-700 mb-16 mt-4 hero-text/g, "text-gray-300 mb-16 mt-4 hero-text");
    }

    // Replace the cards bg-white and bg-gray-50 to solid blue
    text = text.replace(/\bbg-white\b/g, "bg-[#1E2F4D]");
    text = text.replace(/\bbg-gray-50\b/g, "bg-[#1E2F4D]");

    // Update text colors globally so they are legible on blue
    text = text.replace(/\btext-gray-800\b/g, "text-white");
    text = text.replace(/\btext-gray-700\b/g, "text-gray-200");
    text = text.replace(/\btext-gray-600\b/g, "text-gray-300");
    text = text.replace(/\btext-gray-500\b/g, "text-gray-400");
    text = text.replace(/\btext-himatep-dark\b/g, "text-white");
    
    // adjust border colors
    text = text.replace(/\bborder-gray-400\b/g, "border-white/20");
    text = text.replace(/\bborder-gray-200\b/g, "border-white/10");

    if (original !== text) {
        fs.writeFileSync(file, text, "utf8");
    }
});
console.log("Update Complete");
