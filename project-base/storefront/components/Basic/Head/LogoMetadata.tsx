import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';

export const LogoMetadata: FC = () => {
    const { url } = useDomainConfig();
    const logoUrl = 'images/logo.svg';

    return (
        <Head>
            <script
                key="logo-metadata"
                id="logo-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
                        '@context': 'https://schema.org',
                        '@type': 'Organization',
                        url,
                        logo: logoUrl,
                    }),
                }}
            />
        </Head>
    );
};
