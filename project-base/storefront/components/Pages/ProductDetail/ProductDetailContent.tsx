import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { ProductGift } from 'components/Blocks/Product/ProductGift';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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

    return (
        <>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline className="flex vl:flex-row flex-col gap-6">
                    <ProductDetailGallery
                        categoryName={product.categories[0]?.name}
                        flags={product.flags}
                        images={product.images}
                        percentageDiscount={product.price.percentageDiscount}
                        productName={product.name}
                        videoIds={product.productVideos}
                    />

                    <div className="flex w-full flex-1 flex-col gap-5">
                        <ProductDetailInfo
                            brand={product.brand}
                            catalogNumber={product.catalogNumber}
                            name={product.name}
                            namePrefix={product.namePrefix}
                            nameSuffix={product.nameSuffix}
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
                    relatedProducts={product.relatedProducts}
                />

                {isLuigisBoxActive && (
                    <DeferredRecommendedProducts
                        itemUuids={[product.uuid]}
                        recommendationType={TypeRecommendationType.ItemDetail}
                        render={(recommendedProductsContent) => (
                            <Webline>
                                <h2 className="h5 mb-3">{t('Recommended for you')}</h2>
                                {recommendedProductsContent}
                            </Webline>
                        )}
                    />
                )}

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </>
    );
};
