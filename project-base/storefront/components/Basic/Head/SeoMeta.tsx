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
            {description && <meta content={description} name="description" />}
            {ogTitle && <meta content={ogTitle} name="og:title" />}
            {ogDescription && <meta content={ogDescription} name="og:description" />}
            {ogImageUrl && <meta content={ogImageUrl} property="og:image" />}
            {canonicalUrl && canonicalUrl !== currentUrlWithDomain && <link href={canonicalUrl} rel="canonical" />}
        </Head>
    );
};
