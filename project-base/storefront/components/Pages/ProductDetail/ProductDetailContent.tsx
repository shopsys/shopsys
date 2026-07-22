import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { useOpenReviewPopupFromUrl } from 'components/Blocks/ProductReviews/useOpenReviewPopupFromUrl';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { ProductAdditionalServicesSelectionProvider } from 'components/providers/ProductAdditionalServicesSelectionProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import { useGtmFriendlyPageReadyEvent } from 'gtm/factories/useGtmFriendlyPageReadyEvent';
import { useGtmPageReadyEvent } from 'gtm/utils/pageReadyEvents/useGtmPageReadyEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageReadyEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { DeferredComparisonAndWishlistButtons } from './ComparisonAndWishlistButtons/DeferredComparisonAndWishlistButtons';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { DeferredProductDetailAddToCart } from './ProductDetailAddToCart/DeferredProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailTitle } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailPrice } from './ProductDetailPrice';
import { ProductDetailSections } from './ProductDetailSections/ProductDetailSections';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, isProductDetailFetching }) => {
    const { t } = useTranslation();
    const router = useRouter();

    const { isLuigisBoxActive } = useDomainConfig();

    const pageReadyEvent = useGtmFriendlyPageReadyEvent(product);
    useGtmPageReadyEvent(pageReadyEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);
    useOpenReviewPopupFromUrl(product.uuid, product.fullName);

    return (
        <ProductAdditionalServicesSelectionProvider>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline className="flex vl:grid vl:grid-cols-[3fr_2fr] vl:grid-rows-[auto_1fr] flex-col gap-6 vl:gap-y-2">
                    <div className="order-1 vl:col-start-2 vl:row-start-1 flex flex-col">
                        <ProductDetailTitle
                            name={product.name}
                            namePrefix={product.namePrefix}
                            nameSuffix={product.nameSuffix}
                        />
                    </div>

                    <div className="order-2 vl:col-start-1 vl:row-span-2 vl:row-start-1 min-w-0">
                        <ProductDetailGallery
                            categoryName={product.categories[0]?.name}
                            flags={product.flags}
                            images={product.images}
                            percentageDiscount={product.price.percentageDiscount}
                            productName={product.name}
                            videoIds={product.productVideos}
                        />
                    </div>

                    <div className="order-3 vl:col-start-2 vl:row-start-2 flex w-full flex-1 flex-col gap-5">
                        <ProductDetailInfo
                            brand={product.brand}
                            catalogNumber={product.catalogNumber}
                            reviewsSummary={product.reviewsSummary}
                            shortDescription={product.shortDescription}
                            usps={product.usps}
                        />

                        <ProductGift gifts={product.gifts} />

                        <div className="flex flex-col gap-4 rounded-xl bg-background-more p-3 sm:p-6">
                            <ProductDetailPrice productPrice={product.price} />

                            <ProductDetailAvailability
                                availability={product.availability}
                                availableStoresCount={product.availableStoresCount}
                                isInquiryType={product.isInquiryType}
                                isSellingDenied={product.isSellingDenied}
                                storeAvailabilities={product.storeAvailabilities}
                            />

                            <WatchDogButton className="self-start" product={product} />

                            <DeferredProductDetailAddToCart product={product} />

                            <DeferredComparisonAndWishlistButtons product={product} />
                        </div>

                        {product.promotionBuyQuantity !== null && product.promotionFreeQuantity !== null && (
                            <div className="flex flex-col gap-4 rounded-xl bg-background-more p-3 sm:p-6">
                                <div className="font-semibold text-sm text-text-accent">
                                    {t('Buy {{ count }} pieces', { count: product.promotionBuyQuantity })}{' '}
                                    {t('and receive {{ count }} pieces for free.', {
                                        count: product.promotionFreeQuantity,
                                    })}
                                </div>
                            </div>
                        )}
                    </div>
                </Webline>

                <ProductDetailSections
                    description={product.description}
                    files={product.files}
                    parameters={product.parameters}
                    product={product}
                    productFullName={product.fullName}
                    productUuid={product.uuid}
                    relatedProducts={product.relatedProducts}
                    reviewsTotalCount={product.reviewsSummary?.totalCount}
                />

                {isLuigisBoxActive && (
                    <DeferredRecommendedProducts
                        itemUuids={[product.uuid]}
                        recommendationType={TypeRecommendationType.ItemDetail}
                        render={(recommendedProductsContent) => (
                            <Webline>
                                <h2 className="h3 mb-3">{t('Recommended for you')}</h2>
                                {recommendedProductsContent}
                            </Webline>
                        )}
                    />
                )}

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </ProductAdditionalServicesSelectionProvider>
    );
};
