import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeSeoAttributesFragment } from 'graphql/requests/seo/fragments/SeoAttributesFragment.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useRouter } from 'next/router';
import { MetaRobotsContent } from 'types/seo';
import { getImageAlt } from 'utils/imageAltText';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { SEARCH_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';
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
    ogImage?: TypeImageFragment | null;
};

/**
 * Resolves the SEO tags of the page. The SEO page (override for the URL) wins, then the SEO attributes of the entity
 * (product, category, ...) and finally the defaults passed by the page:
 * - title: SEO page → seo.title → seo.h1 → defaultTitle (usually the name of the entity), the current page
 *   information of a paginated list is appended to whichever title wins
 * - description: SEO page → seo.metaDescription → plain text of defaultDescription (the HTML description of the
 *   entity, the perex of a blog article, ...) truncated to whole words
 * - og:url: the canonical URL set in administration, otherwise the current URL without the filter, sort, page and
 *   load-more parameters, so all variants of a listing are shared as the same page (unlike the canonical link)
 * - og:image: SEO page → ogImage (the image of the entity) → organization logo, resized by the "og" preset of
 *   the image resizer; the ALT is the name of the image → og:title → title
 */
export const useSeo = ({
    seo,
    defaultTitle,
    defaultDescription,
    defaultMetaRobots,
    canonicalQueryParams,
    paginationTotalCount,
    paginationPageSize,
    ogImage,
}: UseSeoHookProps) => {
    const { url } = useDomainConfig();
    const router = useRouter();

    const [{ data: settingsData }] = useSettingsQuery();
    const seoPage = useSeoPage();

    const titleSuffix = settingsData?.settings?.seo.titleAddOn;
    const organization = settingsData?.settings?.seo.organization;
    const title = useHeadingWithPagination(
        seoPage?.seo.title || seo?.title || seo?.h1 || defaultTitle,
        paginationTotalCount,
        paginationPageSize,
    );

    const metaRobots = resolveMetaRobots(seoPage?.seo.metaRobots, seo?.metaRobots, defaultMetaRobots);
    const canonicalUrl =
        seoPage?.seo.canonicalUrl || seo?.canonicalUrl || generateCanonicalUrl(router, url, canonicalQueryParams);
    const ogUrl =
        seoPage?.seo.canonicalUrl ||
        seo?.canonicalUrl ||
        generateCanonicalUrl(router, url, [SEARCH_QUERY_PARAMETER_NAME]) ||
        getStringWithoutTrailingSlash(url) + router.asPath;
    // only these formats are processed by the image resizer, social networks do not support SVG anyway
    const ogImageSource = [seoPage?.ogImage, ogImage, organization?.logo].find(
        (image) => image && /\.(jpe?g|png)$/.test(image.url),
    );
    const ogImageUrl = ogImageSource ? `${ogImageSource.url}?preset=og` : undefined;

    return {
        title: title ?? '',
        titleSuffix: titleSuffix ?? '',
        description: seoPage?.seo.metaDescription || getMetaDescription(seo?.metaDescription, defaultDescription),
        metaRobots,
        isNoindex: isNoindexMetaRobots(metaRobots),
        canonicalUrl,
        ogUrl,
        ogSiteName: organization?.name,
        ogTitle: seoPage?.ogTitle,
        ogDescription: seoPage?.ogDescription,
        ogImageUrl,
        ogImageAlt: ogImageUrl
            ? getImageAlt(ogImageSource?.name, getImageAlt(seoPage?.ogTitle, title ?? ''))
            : undefined,
        hreflangLinks: seoPage?.hreflangLinks,
    };
};
