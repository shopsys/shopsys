import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeHreflangLink } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { OgTypeEnum } from 'types/seo';
import { logMessage } from 'utils/errors/logMessage';
import { CanonicalQueryParameters } from 'utils/seo/generateCanonicalUrl';
import { useSeo } from 'utils/seo/useSeo';

type SeoMetaProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
    defaultHreflangLinks?: TypeHreflangLink[];
    ogType?: OgTypeEnum | undefined;
    ogImageUrlDefault?: string | undefined;
};

export const SeoMeta: FC<SeoMetaProps> = ({
    defaultTitle,
    defaultDescription,
    canonicalQueryParams,
    defaultHreflangLinks,
    ogType = OgTypeEnum.Website,
    ogImageUrlDefault,
    children,
}) => {
    const { t } = useTranslation();
    const [areMissingRequiredTagsReported, setAreMissingRequiredTagsReported] = useState(false);

    const {
        title,
        titleSuffix,
        description,
        ogTitle: ogTitleFromProps,
        ogDescription: ogDescriptionFromProps,
        ogImageUrl: ogImageUrlFromProps,
        canonicalUrl,
        hreflangLinks: hreflangLinksSeoPage,
    } = useSeo({
        defaultTitle,
        defaultDescription,
        canonicalQueryParams,
    });

    const currentUri = useRouter().asPath;
    const { url } = useDomainConfig();
    const currentUrlWithDomain = url.substring(0, url.length - 1) + currentUri;

    const hreflangLinks = hreflangLinksSeoPage || defaultHreflangLinks;

    useEffect(() => {
        if (!title && !areMissingRequiredTagsReported) {
            logMessage('Missing required tags', [
                {
                    key: 'tags',
                    data: 'title',
                },
            ]);
            setAreMissingRequiredTagsReported(true);
        }
    }, [title, areMissingRequiredTagsReported]);

    const ogTitle = ogTitleFromProps ?? title;
    const ogDescription = ogDescriptionFromProps ?? description;
    const ogImageUrl = ogImageUrlFromProps ?? ogImageUrlDefault;

    return (
        <Head>
            <title>{`${title} ${titleSuffix}`}</title>

            {description && <meta content={description} name="description" />}

            <meta content={ogType} property="og:type" />
            <meta content={t('metatagSiteName')} property="og:site_name" />
            <meta content={currentUrlWithDomain} property="og:url" />
            {ogTitle && <meta content={ogTitle} name="og:title" />}
            {ogDescription && <meta content={ogDescription} name="og:description" />}
            {ogImageUrl && <meta content={ogImageUrl} property="og:image" />}

            {hreflangLinks?.map(({ hreflang, href }) => (
                <link key={hreflang} href={href} hrefLang={hreflang} rel="alternate" />
            ))}

            {canonicalUrl && canonicalUrl !== currentUrlWithDomain && <link href={canonicalUrl} rel="canonical" />}

            <meta content="summary_large_image" name="twitter:card" />
            <meta content={url} property="twitter:domain" />
            <meta content={currentUrlWithDomain} property="twitter:url" />
            {ogTitle && <meta content={ogTitle} name="twitter:title" />}
            {ogDescription && <meta content={ogDescription} name="twitter:description" />}
            {ogImageUrl && <meta content={ogImageUrl} name="twitter:image" />}
            {children}
        </Head>
    );
};
