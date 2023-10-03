import logMessage from 'helpers/errors/logMessage';
import { CanonicalQueryParameters } from 'helpers/seo/generateCanonicalUrl';
import useSeo from 'hooks/seo/useSeo';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';

type SeoMetaProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
    canonicalQueryParams?: CanonicalQueryParameters;
};

export const SeoMeta: FC<SeoMetaProps> = ({ defaultTitle, defaultDescription, canonicalQueryParams }) => {
    const [areMissingRequiredTagsReported, setAreMissingRequiredTagsReported] = useState(false);

    const { title, titleSuffix, description, ogTitle, ogDescription, ogImageUrl, canonicalUrl } = useSeo({
        defaultTitle,
        defaultDescription,
        canonicalQueryParams,
    });

    const currentUri = useRouter().asPath;
    const { url } = useDomainConfig();
    const currentUrlWithDomain = url.substring(0, url.length - 1) + currentUri;

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
            {description && <meta name="description" content={description} />}
            {ogTitle && <meta name="og:title" content={ogTitle} />}
            {ogDescription && <meta name="og:description" content={ogDescription} />}
            {ogImageUrl && <meta property="og:image" content={ogImageUrl} />}
            {canonicalUrl && canonicalUrl !== currentUrlWithDomain && <link rel="canonical" href={canonicalUrl} />}
        </Head>
    );
};
