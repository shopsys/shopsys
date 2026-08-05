import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/ProductAction';
import { useOpenReviewPopupFromUrl } from 'components/Blocks/ProductReviews/useOpenReviewPopupFromUrl';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageReadyEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { ProductDetailTitle } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailSections } from './ProductDetailSections/ProductDetailSections';
import { ProductVariantsTable } from './ProductDetailVariantsTable';

type ProductDetailMainVariantContentProps = {
    product: TypeMainVariantDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailMainVariantContent: FC<ProductDetailMainVariantContentProps> = ({
    product,
    isProductDetailFetching,
}) => {
    const router = useRouter();

    const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
        if (variant.mainImage) {
            mappedVariantImages.push(variant.mainImage);
        }

        return mappedVariantImages;
    }, [] as TypeImageFragment[]);

    const mainVariantImagesWithVariantImages = [...product.images, ...variantImages];

    const pageReadyEvent = useGtmFriendlyPageReadyEvent(product);
    useGtmPageReadyEvent(pageReadyEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);
    useOpenReviewPopupFromUrl(
        product.uuid,
        product.fullName,
        product.variants.map((variant) => ({ uuid: variant.uuid, fullName: variant.fullName })),
    );

    return (
        <>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline className="flex flex-col gap-5">
                    <div className="order-1 vl:order-2 scroll-mt-fixed-header xl:mt-3" id={PRODUCT_VARIANTS_ID}>
                        <ProductDetailTitle
                            name={product.name}
                            namePrefix={product.namePrefix}
                            nameSuffix={product.nameSuffix}
                        />
                    </div>

                    <div className="order-2 vl:order-1 min-w-0">
                        <ProductDetailGallery
                            categoryName={product.categories[0]?.name}
                            flags={product.flags}
                            images={mainVariantImagesWithVariantImages}
                            percentageDiscount={product.price.percentageDiscount}
                            productName={product.name}
                            videoIds={product.productVideos}
                        />
                    </div>

                    <div className="order-3 vl:-mt-3 flex flex-col gap-5">
                        <ProductDetailInfo
                            catalogNumber={product.catalogNumber}
                            reviewsSummary={product.reviewsSummary}
                        />
                    </div>
                </Webline>

                <ProductVariantsTable variants={product.variants} />

                <ProductDetailSections
                    description={product.description}
                    files={product.files}
                    parameters={product.parameters}
                    productFullName={product.fullName}
                    productUuid={product.uuid}
                    relatedProducts={product.relatedProducts}
                    reviewsTotalCount={product.reviewsSummary?.totalCount}
                    productVariants={product.variants.map((variant) => ({
                        uuid: variant.uuid,
                        fullName: variant.fullName,
                    }))}
                />

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </>
    );
};
