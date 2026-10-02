import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';
import { useRouter } from 'next/router';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { useOrganizationMetadata } from 'utils/seo/useOrganizationMetadata';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

type ArticleMetadataProps = {
    headline: string;
    type?: 'Article' | 'BlogPosting';
    dateModified?: string | null;
    authorJobTitle?: string | null;
    authorImage?: string | null;
    datePublished?: string | null;
    description?: string | null;
    imageUrl?: string | null;
    authorName?: string | null;
};

export const ArticleMetadata: FC<ArticleMetadataProps> = ({
    headline,
    type = 'Article',
    dateModified,
    authorJobTitle,
    authorImage,
    datePublished,
    description,
    imageUrl,
    authorName,
}) => {
    const { url } = useDomainConfig();
    const publisher = useOrganizationMetadata();
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
                        '@type': type,
                        publisher,
                        ...(dateModified && { dateModified }),
                        headline,
                        ...(datePublished && { datePublished }),
                        ...(description && { description }),
                        ...(imageUrl && { image: imageUrl }),
                        ...(authorName && {
                            author: {
                                '@type': 'Person',
                                name: authorName,
                                jobTitle: authorJobTitle || undefined,
                                image: authorImage || undefined,
                            },
                        }),
                        url: currentUrl,
                    }),
                }}
            />
        </Head>
    );
};
