import { getInternationalizedStaticUrls } from 'app/_utils/getInternationalizedStaticUrls';

type HomepageMetadataProps = {
    url: string;
};

// DOCS: https://nextjs.org/docs/14/app/building-your-application/optimizing/metadata#json-ld
export const HomepageMetadataJsonLd: FC<HomepageMetadataProps> = ({ url }) => {
    const [searchUrl] = getInternationalizedStaticUrls(['/search']);

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
