import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import Head from 'next/head';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

export const SearchMetadata: FC = () => {
    const { url } = useShopsysSelector((state) => state.domain);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    return (
        <Head>
            <script
                type="application/ld+json"
                id="search-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify([
                        {
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
                        },
                    ]),
                }}
                key="search-metadata"
            />
        </Head>
    );
};
