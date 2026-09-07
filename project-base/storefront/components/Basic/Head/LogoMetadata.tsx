import Head from 'next/head';
import { useOrganizationMetadata } from 'utils/seo/useOrganizationMetadata';
import { serializeJsonForScriptTag } from 'utils/serialization/serializeJsonForScriptTag';

export const LogoMetadata: FC = () => {
    const organization = useOrganizationMetadata();

    return (
        <Head>
            <script
                key="logo-metadata"
                id="logo-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: serializeJsonForScriptTag({
                        '@context': 'https://schema.org',
                        ...organization,
                    }),
                }}
            />
        </Head>
    );
};
