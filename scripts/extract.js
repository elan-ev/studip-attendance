import { GettextExtractor, JsExtractors } from 'gettext-extractor';
import fs from 'fs';
import { globSync } from 'glob';
import gettextParser from 'gettext-parser';

const extractor = new GettextExtractor();
const localePath = './src/locales';

const jsParser = extractor.createJsParser([
    JsExtractors.callExpression('$gettext', { arguments: { text: 0 } }),
    JsExtractors.callExpression('this.$gettext', { arguments: { text: 0 } })
]);

const files = globSync('src/**/*.{vue,js}');
files.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    jsParser.parseString(content, file);
    const regex = /\$gettext\(\s*['"](.*?)['"]\s*(?=[,)])/gs;
    let match;
    while ((match = regex.exec(content)) !== null) {
        extractor.addMessage({ text: match[1], references: [file] });
    }
});

const currentMessages = extractor.getMessages();
extractor.savePotFile(`${localePath}/template.pot`);

['de', 'en'].forEach(lang => {
    const poPath = `${localePath}/${lang}.po`;
    
    if (!fs.existsSync(poPath)) {
        fs.copyFileSync(`${localePath}/template.pot`, poPath);
        console.log(`✨ ${lang}.po neu erstellt.`);
        return;
    }

    const poFile = fs.readFileSync(poPath);
    const poData = gettextParser.po.parse(poFile);
    const translations = poData.translations[''] || {};
    const newTranslations = {};

    currentMessages.forEach(msg => {
        if (translations[msg.text]) {
            newTranslations[msg.text] = translations[msg.text];
        } else {
            newTranslations[msg.text] = {
                msgid: msg.text,
                msgstr: [""]
            };
            console.log(`➕ Neu in ${lang}.po: "${msg.text}"`);
        }
    });

    Object.keys(translations).forEach(oldKey => {
        if (oldKey !== "" && !currentMessages.find(m => m.text === oldKey)) {
            console.log(`🗑️ Entferne veralteten String: "${oldKey}"`);
        }
    });

    poData.translations[''] = newTranslations;
    
    const output = gettextParser.po.compile(poData);
    fs.writeFileSync(poPath, output);
});

console.log(`🚀 Alle Sprachdateien sind jetzt synchron mit dem Code.`);