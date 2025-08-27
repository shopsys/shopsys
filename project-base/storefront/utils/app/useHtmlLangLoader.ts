import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRouter } from 'next/router';
import { useEffect } from 'react';

export const useHtmlLangLoader = () => {
    const { defaultLocale } = useDomainConfig();
    const router = useRouter();

    useEffect(() => {
        const updateHtmlLang = () => {
            document.documentElement.lang = defaultLocale;
        };

        updateHtmlLang();

        router.events.on('routeChangeComplete', updateHtmlLang);

        return () => {
            router.events.off('routeChangeComplete', updateHtmlLang);
        };
    }, [defaultLocale, router.events]);
};
