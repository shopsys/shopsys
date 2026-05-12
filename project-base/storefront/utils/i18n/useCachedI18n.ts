import { I18nDictionary } from 'next-translate';
import { Dispatch, SetStateAction, useEffect } from 'react';
import { logException } from 'utils/errors/logException';

export type CachedI18nPageProps = {
    __lang?: string;
    __namespaceNames?: string[];
    __namespaces?: Record<string, I18nDictionary>;
    __translationVersion?: string;
};

export type CachedNamespaces = Record<string, I18nDictionary>;

type CachedI18nState = {
    cachedNamespacesLang: string | undefined;
    cachedNamespaces: CachedNamespaces;
    cachedTranslationVersion: string | undefined;
    setCachedLang: (lang: string) => void;
    setCachedNamespacesLang: Dispatch<SetStateAction<string | undefined>>;
    setCachedNamespaces: Dispatch<SetStateAction<CachedNamespaces>>;
    setCachedTranslationVersion: Dispatch<SetStateAction<string | undefined>>;
};

const fillEmptyTranslatesWithKeys = (translates: I18nDictionary): I18nDictionary => {
    for (const key in translates) {
        if (!translates[key]) {
            translates[key] = key;
        }
    }

    return translates;
};

const getVersionQuery = (version: string | undefined): string => (version ? `?v=${encodeURIComponent(version)}` : '');

const getFreshClientTranslates = async (
    locale: string,
    namespace: string,
    version: string | undefined,
): Promise<I18nDictionary> => {
    const versionQuery = getVersionQuery(version);
    const [localTranslatesResponse, userTranslatesResponse] = await Promise.all([
        fetch(`/locales/${locale}/${namespace}.json${versionQuery}`),
        fetch(`/content/locales/${locale}/${namespace}.json${versionQuery}`),
    ]);

    const localTranslates = localTranslatesResponse.status === 200 ? await localTranslatesResponse.json() : {};
    const userTranslates = userTranslatesResponse.status === 200 ? await userTranslatesResponse.json() : {};

    return fillEmptyTranslatesWithKeys({ ...localTranslates, ...userTranslates });
};

const useSyncServerNamespacesToCache = (
    pageProps: CachedI18nPageProps,
    { setCachedLang, setCachedNamespaces, setCachedNamespacesLang, setCachedTranslationVersion }: CachedI18nState,
) => {
    useEffect(() => {
        if (pageProps.__lang) {
            setCachedLang(pageProps.__lang);
        }

        if (pageProps.__namespaces) {
            setCachedNamespaces((currentNamespaces) => ({
                ...currentNamespaces,
                ...pageProps.__namespaces,
            }));
            setCachedNamespacesLang(pageProps.__lang);
            setCachedTranslationVersion(pageProps.__translationVersion);
        }
    }, [pageProps.__lang, pageProps.__namespaces, pageProps.__translationVersion]);
};

const useRefreshClientNamespaces = (
    pageProps: CachedI18nPageProps,
    {
        cachedNamespacesLang,
        cachedNamespaces,
        cachedTranslationVersion,
        setCachedNamespaces,
        setCachedNamespacesLang,
        setCachedTranslationVersion,
    }: CachedI18nState,
) => {
    useEffect(() => {
        if (pageProps.__namespaces) {
            return;
        }

        const namespaceNames = pageProps.__namespaceNames ?? [];
        const missingNamespaceNames = namespaceNames.filter((namespace) => !cachedNamespaces[namespace]);
        const shouldRefreshLocale = !!pageProps.__lang && pageProps.__lang !== cachedNamespacesLang;
        const shouldRefreshNamespaces =
            !!pageProps.__translationVersion && pageProps.__translationVersion !== cachedTranslationVersion;

        const locale = pageProps.__lang;

        if (!locale || (!shouldRefreshLocale && !shouldRefreshNamespaces && missingNamespaceNames.length === 0)) {
            return;
        }

        let isCancelled = false;

        const loadNamespaces = async () => {
            try {
                const loadedNamespaces = Object.fromEntries(
                    await Promise.all(
                        namespaceNames.map(async (namespace) => [
                            namespace,
                            await getFreshClientTranslates(locale, namespace, pageProps.__translationVersion),
                        ]),
                    ),
                );

                if (!isCancelled) {
                    setCachedNamespaces((currentNamespaces) => ({
                        ...currentNamespaces,
                        ...loadedNamespaces,
                    }));
                    setCachedNamespacesLang(locale);
                    setCachedTranslationVersion(pageProps.__translationVersion);
                }
            } catch (error) {
                if (!isCancelled) {
                    logException(error);
                }
            }
        };

        loadNamespaces();

        return () => {
            isCancelled = true;
        };
    }, [
        cachedNamespacesLang,
        cachedNamespaces,
        cachedTranslationVersion,
        pageProps.__lang,
        pageProps.__namespaceNames,
        pageProps.__namespaces,
        pageProps.__translationVersion,
    ]);
};

export const useCachedI18n = (pageProps: CachedI18nPageProps, cachedI18nState: CachedI18nState) => {
    useSyncServerNamespacesToCache(pageProps, cachedI18nState);
    useRefreshClientNamespaces(pageProps, cachedI18nState);
};
