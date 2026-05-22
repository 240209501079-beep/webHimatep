const fs = require("fs");
let cssPath = "public/css/style.css";
let css = fs.readFileSync(cssPath, "utf8");

css = css.replace(/\/\* Membentuk diagonal background pada Hero sesuai gambar \*\/[\s\S]*?\/\* Animasi premium hover untuk card \*\//, '/* Animasi premium hover untuk card */');
css = css.replace(/body\.theme-custom \.hero-diagonal\s*\{[\s\S]*?\}/, '');

css += `
/* Global Batik Blue Background for Sections */
.bg-batik-blue {
    background-color: #1B2945 !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='none' stroke='white' stroke-opacity='0.03' stroke-width='1.2'%3E%3Cpolygon points='40,6 74,40 40,74 6,40'/%3E%3Cpolygon points='40,18 62,40 40,62 18,40'/%3E%3Ccircle cx='40' cy='40' r='5'/%3E%3Ccircle cx='0' cy='0' r='4'/%3E%3Ccircle cx='80' cy='0' r='4'/%3E%3Ccircle cx='0' cy='80' r='4'/%3E%3Ccircle cx='80' cy='80' r='4'/%3E%3Ccircle cx='40' cy='0' r='2.5'/%3E%3Ccircle cx='40' cy='80' r='2.5'/%3E%3Ccircle cx='0' cy='40' r='2.5'/%3E%3Ccircle cx='80' cy='40' r='2.5'/%3E%3C/g%3E%3C/svg%3E") !important;
    background-repeat: repeat !important;
    background-size: 60px !important;
}
`;
fs.writeFileSync(cssPath, css, "utf8");
console.log("CSS Updated correctly.");
