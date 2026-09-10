import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { getImageAlt } from 'utils/imageAltText';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'utils/seo/generateCanonicalUrl';
import { useSeoPage } from 'utils/seo/useSeoPage';

type UseSeoHookProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
};

export const useSeo = ({ defaultTitle, defaultDescription, canonicalQueryParams }: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const [{ data: settingsData }] = useSettingsQuery();
    const seoPage = useSeoPage();

    const preferredTitle = seoPage?.seo.title;
    const preferredDescription = seoPage?.seo.metaDescription;
    const preferredCanonicalUrl = seoPage?.seo.canonicalUrl;
    const preferredOgTitle = seoPage?.ogTitle;
    const preferredOgDescription = seoPage?.ogDescription;
    const preferredOgImageUrl = seoPage?.ogImage?.url;

    const titleSuffix = settingsData?.settings?.seo.titleAddOn;

    const canonicalUrl = preferredCanonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);
    const title = preferredTitle ?? defaultTitle ?? '';

    return {
        title,
        titleSuffix: titleSuffix ?? '',
        description: preferredDescription ?? defaultDescription ?? null,
        ogTitle: preferredOgTitle,
        ogDescription: preferredOgDescription,
        ogImageUrl: preferredOgImageUrl,
        ogImageAlt: preferredOgImageUrl
            ? getImageAlt(seoPage?.ogImage?.name, getImageAlt(preferredOgTitle, title))
            : undefined,
        hreflangLinks: seoPage?.hreflangLinks,
        canonicalUrl,
    };
};
