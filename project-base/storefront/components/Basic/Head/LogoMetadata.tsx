import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';

export const LogoMetadata: FC = () => {
    const { url } = useDomainConfig();
    const logoUrl = 'images/logo.svg';

    return (
        <Head>
            <script
                type="application/ld+json"
                id="logo-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'Organization',
                        url,
                        logo: logoUrl,
                    }),
                }}
                key="logo-metadata"
            />
        </Head>
    );
};
