import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeSeoAttributesFragment } from 'graphql/requests/seo/fragments/SeoAttributesFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { MetaRobotsContent } from 'types/seo';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'utils/seo/generateCanonicalUrl';
import { isNoindexMetaRobots } from 'utils/seo/isNoindexMetaRobots';
import { resolveMetaRobots } from 'utils/seo/resolveMetaRobots';
import { useSeoPage } from 'utils/seo/useSeoPage';

type UseSeoHookProps = {
    seo?: TypeSeoAttributesFragment | null;
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    defaultMetaRobots?: MetaRobotsContent;
    canonicalQueryParams?: CanonicalQueryParameters;
};

export const useSeo = ({
    seo,
    defaultTitle,
    defaultDescription,
    defaultMetaRobots,
    canonicalQueryParams,
}: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const [{ data: settingsData }] = useSettingsQuery();
    const seoPage = useSeoPage();

    const titleSuffix = settingsData?.settings?.seo.titleAddOn;

    const metaRobots = resolveMetaRobots(seoPage?.seo.metaRobots, seo?.metaRobots, defaultMetaRobots);
    const canonicalUrl =
        seoPage?.seo.canonicalUrl || seo?.canonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);

    return {
        title: seoPage?.seo.title ?? defaultTitle ?? '',
        titleSuffix: titleSuffix ?? '',
        description: seoPage?.seo.metaDescription ?? defaultDescription ?? null,
        metaRobots,
        isNoindex: isNoindexMetaRobots(metaRobots),
        canonicalUrl,
        ogTitle: seoPage?.ogTitle,
        ogDescription: seoPage?.ogDescription,
        ogImageUrl: seoPage?.ogImage?.url,
        hreflangLinks: seoPage?.hreflangLinks,
    };
};
