// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getLocalTranslates(locale, namespace) {
    let localTranslates = (await import(`./public/locales/${locale}/${namespace}.json`)).default;

    return fillEmptyTranslatesWithKeys(localTranslates);
}

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getFreshTranslates(locale, namespace) {
    const [localTranslatesResponse, userTranslatesResponse] = await Promise.all([
        fetch(`${process.env.INTERNAL_ENDPOINT}/locales/${locale}/${namespace}.json`),
        fetch(`${process.env.INTERNAL_ENDPOINT}/content/locales/${locale}/${namespace}.json`),
    ]);

    const localTranslates = localTranslatesResponse.status === 200 ? await localTranslatesResponse.json() : {};
    const userTranslates = userTranslatesResponse.status === 200 ? await userTranslatesResponse.json() : {};
    const mergedTranslates = { ...localTranslates, ...userTranslates };

    return fillEmptyTranslatesWithKeys(mergedTranslates);
}

function fillEmptyTranslatesWithKeys(translates) {
    for (let key in translates) {
        if (!translates[key]) {
            translates[key] = key;
        }
    }

    return translates;
}
