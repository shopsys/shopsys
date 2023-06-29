import logMessage from 'helpers/errors/logMessage';
import useSeo from 'hooks/seo/useSeo';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';

type SeoMetaProps = {
    defaultTitle?: string | null;
    defaultDescription?: string | null;
};

export const SeoMeta: FC<SeoMetaProps> = ({ defaultTitle, defaultDescription }) => {
    const [areMissingRequiredTagsReported, setAreMissingRequiredTagsReported] = useState(false);

    const { title, titleSuffix, description, ogTitle, ogDescription, ogImageUrl, canonicalUrl } = useSeo({
        defaultTitle,
        defaultDescription,
    });

    const currentUri = useRouter().asPath;
    const { url } = useDomainConfig();
    const currentUrlWithDomain = url.substring(0, url.length - 1) + currentUri;

    useEffect(() => {
        if (title === null && !areMissingRequiredTagsReported) {
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
            <title>
                {title} {titleSuffix}
            </title>
            {description !== null && <meta name="description" content={description} />}
            {ogTitle !== null && <meta name="og:title" content={ogTitle} />}
            {ogDescription !== null && <meta name="og:description" content={ogDescription} />}
            {ogImageUrl !== null && <meta property="og:image" content={ogImageUrl} />}
            {canonicalUrl !== null && canonicalUrl !== currentUrlWithDomain && (
                <link rel="canonical" href={canonicalUrl} />
            )}
        </Head>
    );
};
