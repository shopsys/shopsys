import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

type ArticleMetadataProps = {
    headline: string;
    datePublished?: string | null;
    description?: string | null;
    imageUrl?: string | null;
    authorName?: string | null;
};

export const ArticleMetadata: FC<ArticleMetadataProps> = ({
    headline,
    datePublished,
    description,
    imageUrl,
    authorName,
}) => {
    const { url } = useDomainConfig();
    const router = useRouter();
    const currentUrl = getStringWithoutTrailingSlash(url) + router.asPath;

    return (
        <Head>
            <script
                key="article-metadata"
                id="article-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: serializeJsonForScriptTag({
                        '@context': 'https://schema.org/',
                        '@type': 'Article',
                        headline,
                        ...(datePublished && { datePublished }),
                        ...(description && { description }),
                        ...(imageUrl && { image: imageUrl }),
                        ...(authorName && { author: { '@type': 'Person', name: authorName } }),
                        url: currentUrl,
                    }),
                }}
            />
        </Head>
    );
};
