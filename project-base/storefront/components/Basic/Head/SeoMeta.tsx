import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { HreflangLink } from 'graphql/types';
import logMessage from 'helpers/errors/logMessage';
import { CanonicalQueryParameters } from 'helpers/seo/generateCanonicalUrl';
import useSeo from 'hooks/seo/useSeo';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';

type SeoMetaProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
    defaultHreflangLinks?: HreflangLink[];
};

export const SeoMeta: FC<SeoMetaProps> = ({
    defaultTitle,
    defaultDescription,
    canonicalQueryParams,
    defaultHreflangLinks,
}) => {
    const [areMissingRequiredTagsReported, setAreMissingRequiredTagsReported] = useState(false);

    const {
        title,
        titleSuffix,
        description,
        ogTitle,
        ogDescription,
        ogImageUrl,
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

    return (
        <Head>
            <title>{`${title} ${titleSuffix}`}</title>

            {description && <meta content={description} name="description" />}

            {ogTitle && <meta content={ogTitle} name="og:title" />}
            {ogDescription && <meta content={ogDescription} name="og:description" />}
            {ogImageUrl && <meta content={ogImageUrl} property="og:image" />}

            {hreflangLinks?.map(({ hreflang, href }) => (
                <link key={hreflang} href={href} hrefLang={hreflang} rel="alternate" />
            ))}

            {canonicalUrl && canonicalUrl !== currentUrlWithDomain && <link href={canonicalUrl} rel="canonical" />}
        </Head>
    );
};
