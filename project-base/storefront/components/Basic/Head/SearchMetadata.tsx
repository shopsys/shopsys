import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';

export const SearchMetadata: FC = () => {
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    return (
        <Head>
            <script
                key="search-metadata"
                id="search-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'WebSite',
                        url,
                        potentialAction: {
                            '@type': 'SearchAction',
                            target: {
                                '@type': 'EntryPoint',
                                urlTemplate: `${searchUrl}?q={q}`,
                            },
                            'query-input': 'required name=q',
                        },
                    }),
                }}
            />
        </Head>
    );
};
