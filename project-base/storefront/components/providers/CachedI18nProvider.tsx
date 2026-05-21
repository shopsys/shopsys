import I18nProvider from 'next-translate/I18nProvider';
import { ReactNode, useMemo, useState } from 'react';
import { resolvedI18nConfig } from 'utils/i18n/registerI18nConfig';
import { CachedI18nPageProps, CachedNamespaces, useCachedI18n } from 'utils/i18n/useCachedI18n';

type CachedI18nProviderProps = {
    children: ReactNode;
    pageProps: CachedI18nPageProps;
};

export const CachedI18nProvider = ({ children, pageProps }: CachedI18nProviderProps) => {
    const [cachedLang, setCachedLang] = useState<string | undefined>(pageProps.__lang);
    const [cachedNamespacesLang, setCachedNamespacesLang] = useState<string | undefined>(
        pageProps.__namespaces ? pageProps.__lang : undefined,
    );
    const [cachedNamespaces, setCachedNamespaces] = useState<CachedNamespaces>(pageProps.__namespaces ?? {});
    const [cachedTranslationVersion, setCachedTranslationVersion] = useState<string | undefined>(
        pageProps.__translationVersion,
    );

    const namespaces = useMemo(
        () => ({
            ...cachedNamespaces,
            ...(pageProps.__namespaces ?? {}),
        }),
        [cachedNamespaces, pageProps.__namespaces],
    );

    const cachedI18nState = {
        cachedNamespacesLang,
        cachedNamespaces,
        cachedTranslationVersion,
        setCachedLang,
        setCachedNamespacesLang,
        setCachedNamespaces,
        setCachedTranslationVersion,
    };

    useCachedI18n(pageProps, cachedI18nState);

    return (
        <I18nProvider config={resolvedI18nConfig} lang={cachedLang} namespaces={namespaces}>
            {children}
        </I18nProvider>
    );
};
