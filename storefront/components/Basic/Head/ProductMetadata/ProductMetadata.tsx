import Head from 'next/head';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { MainVariantDetailType, ProductDetailType } from 'types/product';

type ProductMetadataProps = {
    product: ProductDetailType | MainVariantDetailType;
};

export const ProductMetadata: FC<ProductMetadataProps> = ({ product }) => {
    const router = useRouter();
    const imageUrls = product.images.map((image) => image.sizes?.find((size) => size.size === 'default')?.url);

    return (
        <Head>
            <script
                type="application/ld+json"
                id="product-metadata"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify([
                        {
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
                                priceCurrency: product.price.currencyCode,
                                price: product.price.priceWithVat,
                                itemCondition: 'https://schema.org/NewCondition',
                                availability:
                                    product.availability.status === 'in-stock'
                                        ? 'https://schema.org/InStock'
                                        : 'https://schema.org/OutOfStock',
                            },
                        },
                    ]),
                }}
                key="product-metadata"
            />
        </Head>
    );
};
