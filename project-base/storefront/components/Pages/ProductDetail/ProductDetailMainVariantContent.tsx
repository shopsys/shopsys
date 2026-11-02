import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeliveryOptionsLink } from 'components/Blocks/Popup/DeliveryOptionsPopup/DeliveryOptionsLink';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { ProductEmailDeliveryInfo } from 'components/Blocks/Product/ProductEmailDeliveryInfo';
import { PRODUCT_VARIANTS_ID } from 'components/Blocks/Product/productActionConstants';
import { useOpenReviewPopupFromUrl } from 'components/Blocks/ProductReviews/useOpenReviewPopupFromUrl';
import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageReadyEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import { type MouseEvent } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { isProductSellable } from 'utils/product/isProductSellable';
import { showProductDeliveryOptionsPopup } from 'utils/product/showProductDeliveryOptionsPopup';
import { twMergeCustom } from 'utils/twMerge';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { DeferredProductDetailSecondaryActions } from './ProductDetailSecondaryActions/DeferredProductDetailSecondaryActions';
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
    const { t } = useTranslation();

    const variantImages = product.variants.reduce((mappedVariantImages, variant) => {
        if (variant.mainImage) {
            mappedVariantImages.push(variant.mainImage);
        }

        return mappedVariantImages;
    }, [] as TypeImageFragment[]);

    const mainVariantImagesWithVariantImages = [...product.images, ...variantImages];
    const sellableVariants = product.variants.filter(isProductSellable);
    const deliveryOptionsProducts = product.variants.filter(showProductDeliveryOptionsPopup);

    const scrollToVariants = (event: MouseEvent<HTMLAnchorElement>) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        const variantList = document.getElementById(PRODUCT_VARIANTS_ID);

        if (!variantList) {
            return;
        }

        event.preventDefault();
        window.history.replaceState(window.history.state, '', event.currentTarget.hash);
        variantList.focus({ preventScroll: true });
        variantList.scrollIntoView({
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
            block: 'start',
        });
    };

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
                <Webline className="flex vl:grid vl:grid-cols-[3fr_2fr] vl:grid-rows-[auto_1fr] flex-col gap-6 vl:gap-y-2">
                    <div className="order-1 vl:col-start-2 vl:row-start-1 flex flex-col">
                        <ProductDetailHeading
                            heading={product.seo.h1}
                            name={product.name}
                            namePrefix={product.namePrefix}
                            nameSuffix={product.nameSuffix}
                        />
                    </div>

                    <div className="order-2 vl:col-start-1 vl:row-span-2 vl:row-start-1 min-w-0">
                        <ProductDetailGallery
                            categoryName={product.mainCategory?.name}
                            flags={product.flags}
                            images={mainVariantImagesWithVariantImages}
                            percentageDiscount={product.price.percentageDiscount}
                            productName={product.fullName}
                            videoIds={product.productVideos}
                        />
                    </div>

                    <div className="order-3 vl:col-start-2 vl:row-start-2 flex w-full flex-1 flex-col gap-5">
                        <ProductDetailInfo
                            brand={product.brand}
                            catalogNumber={product.catalogNumber}
                            reviewsSummary={product.reviewsSummary}
                            shortDescription={product.shortDescription}
                        />

                        {product.variants.length > 0 && (
                            <div className="flex flex-col gap-4 rounded-xl bg-background-more p-4 sm:p-5">
                                <a
                                    href={`#${PRODUCT_VARIANTS_ID}`}
                                    className={twMergeCustom(
                                        getButtonClassName('primary', 'large', false, false),
                                        'w-full no-underline hover:no-underline sm:max-w-60',
                                    )}
                                    onClick={scrollToVariants}
                                >
                                    {t('Choose a variant')}
                                </a>

                                {deliveryOptionsProducts.length > 0 && (
                                    <DeliveryOptionsLink products={deliveryOptionsProducts} />
                                )}

                                {deliveryOptionsProducts.length === 0 && sellableVariants.length > 0 && (
                                    <ProductEmailDeliveryInfo />
                                )}
                            </div>
                        )}
                        <div className="px-4 sm:px-5">
                            <DeferredProductDetailSecondaryActions product={product} />
                        </div>
                    </div>
                </Webline>

                <ProductVariantsTable deliveryOptionsProducts={deliveryOptionsProducts} variants={product.variants} />

                <ProductDetailSections
                    description={product.description}
                    files={product.files}
                    initialProductReviews={product.reviews}
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
