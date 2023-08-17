import { useSeoPageQueryApi } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { useSettingsQueryApi } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { generateCanonicalUrl } from 'helpers/seo/generateCanonicalUrl';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useMemo } from 'react';

type UseSeoHookReturn = {
    title: string | null;
    titleSuffix: string | null;
    description: string | null;
    ogTitle: string | null;
    ogDescription: string | null;
    ogImageUrl: string | null;
    canonicalUrl: string | null;
};

type UseSeoHookProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
};

const useSeo = ({ defaultTitle, defaultDescription }: UseSeoHookProps): UseSeoHookReturn => {
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
        pause: pageSlug === null,
    });

    const preferredTitle = seoPageData?.seoPage?.title ?? null;
    const preferredDescription = seoPageData?.seoPage?.metaDescription ?? null;
    const preferredCanonicalUrl = seoPageData?.seoPage?.canonicalUrl ?? null;
    const preferredOgTitle = seoPageData?.seoPage?.ogTitle ?? null;
    const preferredOgDescription = seoPageData?.seoPage?.ogDescription ?? null;
    const preferredOgImageUrl = seoPageData?.seoPage?.ogImage?.sizes[0]?.url ?? null;

    const fallbackTitle = settingsData?.settings?.seo.title ?? null;
    const fallbackDescription = settingsData?.settings?.seo.metaDescription ?? null;
    const fallbackTitleSuffix = settingsData?.settings?.seo.titleAddOn ?? null;

    const canonicalUrl = useMemo(() => {
        return preferredCanonicalUrl ?? generateCanonicalUrl(router, url);
    }, [router, url, preferredCanonicalUrl]);

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
