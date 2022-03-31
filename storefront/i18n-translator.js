// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getLocalTranslates(locale, namespace) {
    return (await import(`./public/locales/${locale}/${namespace}.json`)).default;
}

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export async function getFreshTranslates(locale, namespace) {
    const [localTranslatesResponse, userTranslatesResponse] = await Promise.all([
        fetch(`http://webserver:8080/locales/${locale}/${namespace}.json`),
        fetch(`http://webserver:8080/content/locales/${locale}/${namespace}.json`),
    ]);

    const localTranslates = await localTranslatesResponse.json();
    const userTranslates = await userTranslatesResponse.json();

    return { ...localTranslates, ...userTranslates };
}
