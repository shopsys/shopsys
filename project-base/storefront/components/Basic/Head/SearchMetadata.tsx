import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';

export const SearchMetadata: FC = () => {
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    return (
        <Head>
            <script
                type="application/ld+json"
                id="search-metadata"
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
                key="search-metadata"
            />
        </Head>
    );
};
