import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

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
