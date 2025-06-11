import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductVariantsTable } from './ProductDetailVariantsTable';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

type ProductDetailMainVariantContentProps = {
    product: TypeMainVariantDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({
    product,
    isProductDetailFetching,
}) => {
    const router = useRouter();
    const mainVariantImagesWithVariantImages = useMemo(() => {
        const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
            if (variant.mainImage) {
                mappedVariantImages.push(variant.mainImage);
            }

            return mappedVariantImages;
        }, [] as TypeImageFragment[]);

        return [...product.images, ...variantImages];
    }, [product]);

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline>
                    <ProductDetailGallery
                        categoryName={product.categories[0]?.name}
                        flags={product.flags}
                        images={mainVariantImagesWithVariantImages}
                        percentageDiscount={product.price.percentageDiscount}
                        productName={product.name}
                        videoIds={product.productVideos}
                    />
                </Webline>

                <Webline>
                    <ProductDetailInfo
                        catalogNumber={product.catalogNumber}
                        name={product.name}
                        namePrefix={product.namePrefix}
                        nameSuffix={product.nameSuffix}
                    />
                </Webline>

                <ProductVariantsTable variants={product.variants} />

                <ProductDetailTabs
                    description={product.description}
                    files={product.files}
                    parameters={product.parameters}
                    relatedProducts={product.relatedProducts}
                />

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </>
    );
};
