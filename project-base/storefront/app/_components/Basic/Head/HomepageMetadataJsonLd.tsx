import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { getInternationalizedStaticUrls } from 'app/_utils/getInternationalizedStaticUrls';

// DOCS: https://nextjs.org/docs/14/app/building-your-application/optimizing/metadata#json-ld
export const HomepageMetadataJsonLd: FC = async () => {
    const { url } = await getDomainConfig();
    const [searchUrl] = await getInternationalizedStaticUrls(['/search']);

    const jsonLd = {
        '@context': 'https://schema.org/',
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
    };

    return (
        <script
            key="product-metadata"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
            id="product-metadata"
            type="application/ld+json"
        />
    );
};
