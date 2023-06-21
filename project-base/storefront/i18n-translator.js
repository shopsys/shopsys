// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getLocalTranslates(locale, namespace) {
    let localTranslates = (await import(`./public/locales/${locale}/${namespace}.json`)).default;

    return fillEmptyTranslatesWithKeys(localTranslates);
}

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getFreshTranslates(locale, namespace) {
    const ENDPOINT_URL = process.env.INTERNAL_GRAPHQL_ENDPOINT.substring(
        0,
        process.env.INTERNAL_GRAPHQL_ENDPOINT.indexOf('/graphql'),
    );

    const [localTranslatesResponse, userTranslatesResponse] = await Promise.all([
        fetch(`${ENDPOINT_URL}/locales/${locale}/${namespace}.json`),
        fetch(`${ENDPOINT_URL}/content/locales/${locale}/${namespace}.json`),
    ]);

    const localTranslates = localTranslatesResponse.status === 200 ? await localTranslatesResponse.json() : {};
    const userTranslates = userTranslatesResponse.status === 200 ? await userTranslatesResponse.json() : {};
    const mergedTranslates = { ...localTranslates, ...userTranslates };

    return fillEmptyTranslatesWithKeys(mergedTranslates);
}

function fillEmptyTranslatesWithKeys(translates) {
    for (let key in translates) {
        if (translates[key] === undefined || translates[key] === null || translates[key] === '') {
            translates[key] = key;
        }
    }

    return translates;
}
