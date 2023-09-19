import { useSeoPageQueryApi, useSettingsQueryApi } from 'graphql/generated';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'helpers/seo/generateCanonicalUrl';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type UseSeoHookReturn = {
    title: string | null | undefined;
    titleSuffix: string | null | undefined;
    description: string | null | undefined;
    ogTitle: string | null | undefined;
    ogDescription: string | null | undefined;
    ogImageUrl: string | null | undefined;
    canonicalUrl: string | null | undefined;
};

type UseSeoHookProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
};

const useSeo = ({ defaultTitle, defaultDescription, canonicalQueryParams }: UseSeoHookProps): UseSeoHookReturn => {
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
    const preferredOgImageUrl = seoPageData?.seoPage?.ogImage?.sizes[0]?.url;

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
        canonicalUrl,
    };
};

export default useSeo;
