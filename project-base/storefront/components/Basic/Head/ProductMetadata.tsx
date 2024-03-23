import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { MainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { ProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { AvailabilityStatusEnum } from 'graphql/types';
import Head from 'next/head';
import { useRouter } from 'next/router';

type ProductMetadataProps = {
    product: ProductDetailFragment | MainVariantDetailFragment;
};

export const ProductMetadata: FC<ProductMetadataProps> = ({ product }) => {
    const { currencyCode } = useDomainConfig();
    const router = useRouter();

    return (
        <Head>
            <script
                key="product-metadata"
                id="product-metadata"
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify({
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
                            url: router.asPath,
                            priceCurrency: currencyCode,
                            price: product.price.priceWithVat,
                            itemCondition: 'https://schema.org/NewCondition',
                            availability:
                                product.availability.status === AvailabilityStatusEnum.InStock
                                    ? 'https://schema.org/InStock'
                                    : 'https://schema.org/OutOfStock',
                        },
                    }),
                }}
            />
        </Head>
    );
};
