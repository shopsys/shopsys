import { AvailabilityStatusEnumApi, MainVariantDetailFragmentApi, ProductDetailFragmentApi } from 'graphql/generated';
import { useDomainConfig } from 'hooks/useDomainConfig';
import Head from 'next/head';
import { useRouter } from 'next/router';

type ProductMetadataProps = {
    product: ProductDetailFragmentApi | MainVariantDetailFragmentApi;
};

export const ProductMetadata: FC<ProductMetadataProps> = ({ product }) => {
    const { currencyCode } = useDomainConfig();
    const router = useRouter();
    const imageUrls = product.images.map((image) => image.sizes.find((size) => size.size === 'default')?.url);

    return (
        <Head>
            <script
                type="application/ld+json"
                id="product-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
                        '@context': 'https://schema.org/',
                        '@type': 'Product',
                        name: product.fullName,
                        image: imageUrls,
                        description: product.description,
                        sku: product.catalogNumber,
                        mpn: product.ean,
                        brand: {
                            '@type': 'Brand',
                            name: product.brand?.name,
                        },
                        offers: {
                            '@type': 'Offer',
                            url: router.asPath,
                            priceCurrency: currencyCode,
                            price: product.price.priceWithVat,
                            itemCondition: 'https://schema.org/NewCondition',
                            availability:
                                product.availability.status === AvailabilityStatusEnumApi.InStockApi
                                    ? 'https://schema.org/InStock'
                                    : 'https://schema.org/OutOfStock',
                        },
                    }),
                }}
                key="product-metadata"
            />
        </Head>
    );
};
