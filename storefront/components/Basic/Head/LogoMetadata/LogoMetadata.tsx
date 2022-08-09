import Head from 'next/head';
import { FC } from 'react';
import { useShopsysSelector } from 'redux/main';

export const LogoMetadata: FC = () => {
    const { url } = useShopsysSelector((state) => state.domain);
    const logoUrl = url + 'images/logo.svg';

    return (
        <Head>
            <script
                type="application/ld+json"
                id="logo-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify([
                        {
                            '@context': 'https://schema.org',
                            '@type': 'Organization',
                            url,
                            logo: logoUrl,
                        },
                    ]),
                }}
                key="logo-metadata"
            />
        </Head>
    );
};
