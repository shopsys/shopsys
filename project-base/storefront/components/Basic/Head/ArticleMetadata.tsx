import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';

type ArticleMetadataProps = {
    headline: string;
    datePublished: string;
    description?: string | null;
    imageUrl?: string | null;
};

export const ArticleMetadata: FC<ArticleMetadataProps> = ({ headline, datePublished, description, imageUrl }) => {
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
                    __html: JSON.stringify({
                        '@context': 'https://schema.org/',
                        '@type': 'Article',
                        headline,
                        datePublished,
                        ...(description && { description }),
                        ...(imageUrl && { image: imageUrl }),
                        url: currentUrl,
                    }),
                }}
            />
        </Head>
    );
};
