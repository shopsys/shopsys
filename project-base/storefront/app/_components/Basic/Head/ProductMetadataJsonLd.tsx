import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.ssr';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.ssr';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { headers } from 'next/headers';

type ProductMetadataProps = {
    product: TypeProductDetailFragment | TypeMainVariantDetailFragment;
};

// DOCS: https://nextjs.org/docs/14/app/building-your-application/optimizing/metadata#json-ld
export const ProductMetadataJsonLd: FC<ProductMetadataProps> = async ({ product }) => {
    const headersList = await headers();
    const { currencyCode } = getDomainConfig(headersList.get('host')!);
    const asPath = headersList.get('x-asPath');

    const jsonLd = {
        '@context': 'https://schema.org/',
        '@type': 'Product',
        name: product.fullName,
        image: product.images.length > 0 ? product.images[0].url : null,
        description: product.description,
        sku: product.catalogNumber,
        mpn: product.ean,
        brand: {
            '@type': 'Brand',
            name: product.brand?.name,
        },
        offers: {
            '@type': 'Offer',
            url: asPath,
            priceCurrency: currencyCode,
            price: product.price.priceWithVat,
            itemCondition: 'https://schema.org/NewCondition',
            availability:
                product.availability.status === TypeAvailabilityStatusEnum.InStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
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
