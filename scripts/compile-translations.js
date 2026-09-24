import fs from 'fs';
import gettextParser from 'gettext-parser';

const locales = ['de', 'en'];
const localeDir = './src/locales';
const finalTranslations = {};

console.log('--- Kompiliere Übersetzungen ---');

locales.forEach(lang => {
    const poFilePath = `${localeDir}/${lang}.po`;
    
    if (fs.existsSync(poFilePath)) {
        const input = fs.readFileSync(poFilePath);
        const po = gettextParser.po.parse(input);
        
        finalTranslations[lang] = {};
        
        const translations = po.translations[''];
        if (translations) {
            for (const msgid in translations) {
                if (msgid === "") continue;
                const msgstr = translations[msgid].msgstr[0];
                if (msgstr) {
                    finalTranslations[lang][msgid] = msgstr;
                }
            }
        }
        console.log(`✅ ${lang}.po verarbeitet.`);
    } else {
        console.warn(`⚠️ ${lang}.po nicht gefunden, überspringe...`);
    }
});

fs.writeFileSync(
    `${localeDir}/translations.json`, 
    JSON.stringify(finalTranslations, null, 2)
);

console.log(`🚀 Fertig! src/locales/translations.json wurde aktualisiert.`);