import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSeoPageQuery } from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { extractSeoPageSlugFromUrl } from 'utils/seo/extractSeoPageSlugFromUrl';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'utils/seo/generateCanonicalUrl';

type UseSeoHookProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
};

export const useSeo = ({ defaultTitle, defaultDescription, canonicalQueryParams }: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const pageSlug = extractSeoPageSlugFromUrl(router.asPath, url);

    const [{ data: settingsData }] = useSettingsQuery();
    const [{ data: seoPageData }] = useSeoPageQuery({
        variables: {
            pageSlug: pageSlug!,
        },
        pause: !pageSlug,
    });

    const preferredTitle = seoPageData?.seoPage?.seo.title;
    const preferredDescription = seoPageData?.seoPage?.seo.metaDescription;
    const preferredCanonicalUrl = seoPageData?.seoPage?.seo.canonicalUrl;
    const preferredOgTitle = seoPageData?.seoPage?.ogTitle;
    const preferredOgDescription = seoPageData?.seoPage?.ogDescription;
    const preferredOgImageUrl = seoPageData?.seoPage?.ogImage?.url;

    const titleSuffix = settingsData?.settings?.seo.titleAddOn;

    const canonicalUrl = preferredCanonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);

    return {
        title: preferredTitle ?? defaultTitle ?? '',
        titleSuffix: titleSuffix ?? '',
        description: preferredDescription ?? defaultDescription ?? null,
        ogTitle: preferredOgTitle,
        ogDescription: preferredOgDescription,
        ogImageUrl: preferredOgImageUrl,
        hreflangLinks: seoPageData?.seoPage?.hreflangLinks,
        canonicalUrl,
    };
};
