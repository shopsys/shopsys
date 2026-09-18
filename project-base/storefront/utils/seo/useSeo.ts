import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeSeoAttributesFragment } from 'graphql/requests/seo/fragments/SeoAttributesFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { MetaRobotsContent } from 'types/seo';
import { CanonicalQueryParameters, generateCanonicalUrl } from 'utils/seo/generateCanonicalUrl';
import { getMetaDescription } from 'utils/seo/getMetaDescription';
import { isNoindexMetaRobots } from 'utils/seo/isNoindexMetaRobots';
import { resolveMetaRobots } from 'utils/seo/resolveMetaRobots';
import { useHeadingWithPagination } from 'utils/seo/useHeadingWithPagination';
import { useSeoPage } from 'utils/seo/useSeoPage';

type UseSeoHookProps = {
    seo?: TypeSeoAttributesFragment | null;
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    defaultMetaRobots?: MetaRobotsContent;
    canonicalQueryParams?: CanonicalQueryParameters;
    paginationTotalCount?: number;
    paginationPageSize?: number;
};

/**
 * Resolves the SEO tags of the page. The SEO page (override for the URL) wins, then the SEO attributes of the entity
 * (product, category, ...) and finally the defaults passed by the page:
 * - title: SEO page → seo.title → seo.h1 → defaultTitle (usually the name of the entity), the current page
 *   information of a paginated list is appended to whichever title wins
 * - description: SEO page → seo.metaDescription → plain text of defaultDescription (the HTML description of the
 *   entity, the perex of a blog article, ...) truncated to whole words
 */
export const useSeo = ({
    seo,
    defaultTitle,
    defaultDescription,
    defaultMetaRobots,
    canonicalQueryParams,
    paginationTotalCount,
    paginationPageSize,
}: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const [{ data: settingsData }] = useSettingsQuery();
    const seoPage = useSeoPage();

    const titleSuffix = settingsData?.settings?.seo.titleAddOn;
    const title = useHeadingWithPagination(
        seoPage?.seo.title || seo?.title || seo?.h1 || defaultTitle,
        paginationTotalCount,
        paginationPageSize,
    );

    const metaRobots = resolveMetaRobots(seoPage?.seo.metaRobots, seo?.metaRobots, defaultMetaRobots);
    const canonicalUrl =
        seoPage?.seo.canonicalUrl || seo?.canonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);

    return {
        title: title ?? '',
        titleSuffix: titleSuffix ?? '',
        description: seoPage?.seo.metaDescription || getMetaDescription(seo?.metaDescription, defaultDescription),
        metaRobots,
        isNoindex: isNoindexMetaRobots(metaRobots),
        canonicalUrl,
        ogTitle: seoPage?.ogTitle,
        ogDescription: seoPage?.ogDescription,
        ogImageUrl: seoPage?.ogImage?.url,
        hreflangLinks: seoPage?.hreflangLinks,
    };
};
