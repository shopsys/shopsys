import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import Head from 'next/head';
import { getStringWithoutTrailingSlash } from 'utils/parsing/stringWIthoutSlash';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

export const LogoMetadata: FC = () => {
    const { url } = useDomainConfig();
    const logoUrl = `${getStringWithoutTrailingSlash(url)}/images/logo.svg`;

    return (
        <Head>
            <script
                key="logo-metadata"
                id="logo-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: serializeJsonForScriptTag({
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
