import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeSeoAttributesFragment } from 'graphql/requests/seo/fragments/SeoAttributesFragment.generated';
import { TypeHreflangLink } from 'graphql/types';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { MetaRobotsContent, OgTypeEnum } from 'types/seo';
import { logMessage } from 'utils/errors/logMessage';
import { CanonicalQueryParameters } from 'utils/seo/generateCanonicalUrl';
import { getDocumentTitle } from 'utils/seo/getDocumentTitle';
import { useSeo } from 'utils/seo/useSeo';

type SeoMetaProps = {
    seo?: TypeSeoAttributesFragment | null;
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    defaultMetaRobots?: MetaRobotsContent;
    canonicalQueryParams?: CanonicalQueryParameters;
    defaultHreflangLinks?: TypeHreflangLink[];
    paginationTotalCount?: number;
    paginationPageSize?: number;
    ogType?: OgTypeEnum | undefined;
    ogImage?: TypeImageFragment | null;
};

export const SeoMeta: FC<SeoMetaProps> = ({
    seo,
    defaultTitle,
    defaultDescription,
    defaultMetaRobots,
    canonicalQueryParams,
    defaultHreflangLinks,
    paginationTotalCount,
    paginationPageSize,
    ogType = OgTypeEnum.Website,
    ogImage,
    children,
}) => {
    const [areMissingRequiredTagsReported, setAreMissingRequiredTagsReported] = useState(false);

    const {
        title,
        titleSuffix,
        description,
        ogTitle: ogTitleFromProps,
        ogDescription: ogDescriptionFromProps,
        ogImageUrl,
        ogImageAlt,
        metaRobots,
        isNoindex,
        canonicalUrl,
        ogUrl,
        ogSiteName,
        hreflangLinks: hreflangLinksSeoPage,
    } = useSeo({
        seo,
        defaultTitle,
        defaultDescription,
        defaultMetaRobots,
        canonicalQueryParams,
        paginationTotalCount,
        paginationPageSize,
        ogImage,
    });

    const currentUri = useRouter().asPath;
    const { url, defaultLocale } = useDomainConfig();
    const currentUrlWithDomain = url.substring(0, url.length - 1) + currentUri;

    const hreflangLinks = hreflangLinksSeoPage || defaultHreflangLinks;

    useEffect(() => {
        if (!title && !titleSuffix && !areMissingRequiredTagsReported) {
            logMessage('Missing required tags', [
                {
                    key: 'tags',
                    data: 'title',
                },
            ]);
            setAreMissingRequiredTagsReported(true);
        }
    }, [title, titleSuffix, areMissingRequiredTagsReported]);

    const ogTitle = ogTitleFromProps ?? title;
    const ogDescription = ogDescriptionFromProps ?? description;
    // Open Graph expects the ll_TT format, so the most likely country of the domain language is added (e.g. cs → cs_CZ)
    const { language, region } = new Intl.Locale(defaultLocale).maximize();
    const ogLocale = region ? `${language}_${region}` : language;

    return (
        <Head>
            <title>{getDocumentTitle(title, titleSuffix)}</title>

            {description && <meta content={description} name="description" />}
            {metaRobots && <meta content={metaRobots} name="robots" />}

            {!isNoindex && (
                <>
                    <link href={canonicalUrl || currentUrlWithDomain} rel="canonical" />

                    {hreflangLinks?.map(({ hreflang, href }) => (
                        <link key={hreflang} href={href} hrefLang={hreflang} rel="alternate" />
                    ))}
                    {hreflangLinks && hreflangLinks.length > 0 && (
                        <link href={canonicalUrl || currentUrlWithDomain} hrefLang="x-default" rel="alternate" />
                    )}
                </>
            )}

            <meta content={ogType} property="og:type" />
            {ogSiteName && <meta content={ogSiteName} property="og:site_name" />}
            <meta content={ogLocale} property="og:locale" />
            <meta content={ogUrl} property="og:url" />
            {ogTitle && <meta content={ogTitle} property="og:title" />}
            {ogDescription && <meta content={ogDescription} property="og:description" />}
            {ogImageUrl && <meta content={ogImageUrl} property="og:image" />}
            {ogImageUrl && ogImageAlt && <meta content={ogImageAlt} property="og:image:alt" />}

            <meta content={ogImageUrl ? 'summary_large_image' : 'summary'} name="twitter:card" />
            <meta content={new URL(url).hostname} name="twitter:domain" />
            <meta content={ogUrl} name="twitter:url" />
            {ogTitle && <meta content={ogTitle} name="twitter:title" />}
            {ogDescription && <meta content={ogDescription} name="twitter:description" />}
            {ogImageUrl && <meta content={ogImageUrl} name="twitter:image" />}
            {ogImageUrl && ogImageAlt && <meta content={ogImageAlt} name="twitter:image:alt" />}

            {children}
        </Head>
    );
};
