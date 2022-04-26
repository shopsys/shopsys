// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getLocalTranslates(locale, namespace) {
    return (await import(`./public/locales/${locale}/${namespace}.json`)).default;
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

    const localTranslates = await localTranslatesResponse.json();
    const userTranslates = await userTranslatesResponse.json();

    return { ...localTranslates, ...userTranslates };
}
