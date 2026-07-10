type Translations = Record<string, string>;

// ===== INTERNAL HELPERS (for caching and loading) =====

let cachedTranslations: Translations | null = null;
let cachedLocale: string | null = null;

// Loads translations from public/locales/{locale}/common.json
const loadTranslations = (locale: string): Cypress.Chainable<Translations> => {
    if (cachedTranslations && cachedLocale === locale) {
        return cy.wrap(cachedTranslations);
    }

    return cy.readFile(`public/locales/${locale}/common.json`).then((content: Translations) => {
        cachedTranslations = content;
        cachedLocale = locale;
        return cy.wrap(content);
    });
};

let cachedDemoDataTranslations: Record<string, Record<string, string>> | null = null;

// Parses .po file content and extracts msgid -> msgstr mappings
const parsePoFile = (content: string): Record<string, string> => {
    const translations: Record<string, string> = {};
    const lines = content.split('\n');
    let currentMsgid = '';
    let currentMsgstr = '';
    let inMsgid = false;
    let inMsgstr = false;

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();

        if (line.startsWith('msgid ')) {
            if (currentMsgid && currentMsgstr) {
                translations[currentMsgid] = currentMsgstr;
            }

            const match = line.match(/^msgid\s+"(.*)"/);
            if (match) {
                currentMsgid = match[1].replace(/\\n/g, '\n').replace(/\\"/g, '"');
            } else if (line === 'msgid ""') {
                currentMsgid = '';
            }
            inMsgid = true;
            inMsgstr = false;
            currentMsgstr = '';
        } else if (line.startsWith('msgstr ')) {
            const match = line.match(/^msgstr\s+"(.*)"/);
            if (match) {
                currentMsgstr = match[1].replace(/\\n/g, '\n').replace(/\\"/g, '"');
            } else if (line === 'msgstr ""') {
                currentMsgstr = '';
            }
            inMsgid = false;
            inMsgstr = true;
        } else if (line.startsWith('"') && line.endsWith('"')) {
            const value = line.slice(1, -1).replace(/\\n/g, '\n').replace(/\\"/g, '"');
            if (inMsgid) {
                currentMsgid += value;
            } else if (inMsgstr) {
                currentMsgstr += value;
            }
        } else if (line === '' || line.startsWith('#')) {
            if (currentMsgid && currentMsgstr) {
                translations[currentMsgid] = currentMsgstr;
            }
            currentMsgid = '';
            currentMsgstr = '';
            inMsgid = false;
            inMsgstr = false;
        }
    }

    if (currentMsgid && currentMsgstr) {
        translations[currentMsgid] = currentMsgstr;
    }

    return translations;
};

// Loads demo data translations from dataFixtures.{locale}.po files (mounted at /app/app-translations/)
const loadDemoDataTranslations = (): Cypress.Chainable<Record<string, Record<string, string>>> => {
    if (cachedDemoDataTranslations) {
        return cy.wrap(cachedDemoDataTranslations);
    }

    return cy.task<string[]>('getAvailablePoLocales').then((locales) => {
        const translationsByLocale: Record<string, Record<string, string>> = {};

        let chain = cy.wrap(null);

        locales.forEach((locale) => {
            chain = chain.then(() => {
                return cy
                    .readFile(`app-translations/dataFixtures.${locale}.po`, { timeout: 10000, log: false })
                    .then((content: string) => {
                        translationsByLocale[locale] = parsePoFile(content);
                        return cy.wrap(null);
                    });
            });
        });

        return chain.then(() => {
            cachedDemoDataTranslations = translationsByLocale;
            return cy.wrap(translationsByLocale);
        });
    });
};

// ===== UNIFIED TRANSLATION API =====

// Parameter substitution helper
const applyParams = (text: string, params?: Record<string, string | number>): string => {
    if (!params) return text;
    let result = text;
    Object.entries(params).forEach(([key, value]) => {
        result = result.replace(new RegExp(`{{ ${key} }}`, 'g'), String(value));
        result = result.replace(new RegExp(`{{${key}}}`, 'g'), String(value));
    });
    return result;
};

// Unified translation with smart fallback chain (common.json → .po files)
const translateWithFallback = (key: string, params?: Record<string, string | number>): Cypress.Chainable<string> => {
    const locale = Cypress.env('TEST_LOCALE');

    return loadTranslations(locale).then((commonTranslations) => {
        // 1. Try common.json first
        if (commonTranslations[key]) {
            return cy.wrap(applyParams(commonTranslations[key], params));
        }

        // 2. Fallback to .po files (demo data)
        return loadDemoDataTranslations().then((poTranslations) => {
            const localeTranslations = poTranslations[locale];
            if (localeTranslations?.[key]) {
                let result = localeTranslations[key];
                // Handle %locale%, %counter% placeholders
                result = result.replace(/%locale%/g, locale);
                result = result.replace(/%counter%/g, '1');
                return cy.wrap(applyParams(result, params));
            }

            // 3. Return key if not found
            return cy.wrap(key);
        });
    });
};

// Alias for one-off translations (uses same fallback logic)
export const t = translateWithFallback;

// Export type for translations structure
export type TranslationsType = {
    placeholder: Record<string, string>;
    payment: Record<string, string>;
    transport: Record<string, string>;
    transportGroup: Record<string, string>;
    order: {
        created: string;
        orderNumber: string;
        confirmation: Record<string, string>;
        promoCode: string;
    };
    link: Record<string, string>;
    button: Record<string, string>;
    filter: Record<string, string>;
    toast: {
        success: Record<string, string>;
        error: Record<string, string>;
        info: Record<string, string>;
    };
};

// Load all translations and return typed structure
export const loadAllTranslations = (): Cypress.Chainable<TranslationsType> => {
    // Import translationKeys dynamically
    const { translationKeys } = require('../fixtures/translationKeys');

    // Build a flat list of all keys and track their category/field mapping
    const allKeys: string[] = [];
    const keyMap: Record<string, [string, string]> = {};

    Object.entries(translationKeys).forEach(([category, fields]) => {
        Object.entries(fields as Record<string, any>).forEach(([field, value]) => {
            // Handle nested structures like order.confirmation
            if (typeof value === 'object' && value !== null) {
                Object.entries(value).forEach(([nestedField, nestedValue]) => {
                    if (typeof nestedValue === 'string') {
                        allKeys.push(nestedValue);
                        keyMap[nestedValue] = [`${category}.${field}`, nestedField];
                    }
                });
            } else if (typeof value === 'string') {
                allKeys.push(value);
                keyMap[value] = [category, field];
            }
        });
    });

    // Build result structure
    const result: Record<string, any> = {};

    // Initialize nested structures
    Object.keys(translationKeys).forEach((category) => {
        result[category] = {};
        const fields = translationKeys[category as keyof typeof translationKeys];
        Object.entries(fields).forEach(([field, value]) => {
            if (typeof value === 'object' && value !== null) {
                result[category][field] = {};
            }
        });
    });

    // Load all translations sequentially (required by Cypress)
    let chain = cy.wrap(null);

    allKeys.forEach((key) => {
        chain = chain.then(() => {
            return translateWithFallback(key).then((translated) => {
                const [categoryPath, field] = keyMap[key];

                // Handle nested paths like "order.confirmation"
                if (categoryPath.includes('.')) {
                    const [category, nestedCategory] = categoryPath.split('.');
                    if (!result[category][nestedCategory]) {
                        result[category][nestedCategory] = {};
                    }
                    result[category][nestedCategory][field] = translated;
                } else {
                    result[categoryPath][field] = translated;
                }

                return cy.wrap(null);
            });
        });
    });

    return chain.then(() => cy.wrap(result)) as any;
};
