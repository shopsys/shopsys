import { useSeoPageQueryApi, useSettingsQueryApi } from 'graphql/generated';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'helpers/seo/generateCanonicalUrl';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type UseSeoHookProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
};

const useSeo = ({ defaultTitle, defaultDescription, canonicalQueryParams }: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const pageSlug = useMemo(() => {
        return extractSeoPageSlugFromUrl(router.asPath, url);
    }, [router.asPath, url]);

    const [{ data: settingsData }] = useSettingsQueryApi();
    const [{ data: seoPageData }] = useSeoPageQueryApi({
        variables: {
            pageSlug: pageSlug!,
        },
        pause: !pageSlug,
    });

    const preferredTitle = seoPageData?.seoPage?.title;
    const preferredDescription = seoPageData?.seoPage?.metaDescription;
    const preferredCanonicalUrl = seoPageData?.seoPage?.canonicalUrl;
    const preferredOgTitle = seoPageData?.seoPage?.ogTitle;
    const preferredOgDescription = seoPageData?.seoPage?.ogDescription;
    const preferredOgImageUrl = seoPageData?.seoPage?.ogImage?.url;

    const fallbackTitle = settingsData?.settings?.seo.title;
    const fallbackDescription = settingsData?.settings?.seo.metaDescription;
    const fallbackTitleSuffix = settingsData?.settings?.seo.titleAddOn;

    const canonicalUrl = preferredCanonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);

    return {
        title: preferredTitle ?? defaultTitle ?? fallbackTitle,
        titleSuffix: fallbackTitleSuffix,
        description: preferredDescription ?? defaultDescription ?? fallbackDescription,
        ogTitle: preferredOgTitle,
        ogDescription: preferredOgDescription,
        ogImageUrl: preferredOgImageUrl,
        hreflangLinks: seoPageData?.seoPage?.hreflangLinks,
        canonicalUrl,
    };
};

export default useSeo;
