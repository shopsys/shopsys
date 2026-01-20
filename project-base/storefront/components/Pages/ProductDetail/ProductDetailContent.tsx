import { DeferredComparisonAndWishlistButtons } from './ComparisonAndWishlistButtons/DeferredComparisonAndWishlistButtons';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { DeferredProductDetailAddToCart } from './ProductDetailAddToCart/DeferredProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailPrice } from './ProductDetailPrice';
import { ProductDetailSections } from './ProductDetailSections/ProductDetailSections';
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
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, isProductDetailFetching }) => {
    const { t } = useTranslation();
    const router = useRouter();

    const { isLuigisBoxActive } = useDomainConfig();

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            <ProductMetadata product={product} />

            <VerticalStack gap="md">
                <Webline className="vl:flex-row flex flex-col gap-6">
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

                        <div className="bg-background-more flex flex-col gap-4 rounded-xl p-3 sm:p-6">
                            {product.gifts.length > 0 && (
                                <>
                                    <p className="h3 mb-3">{t('Gifts')}</p>
                                    <div>
                                        {product.gifts.map((gift) => (
                                            <ProductGift key={gift.uuid} gift={gift} />
                                        ))}
                                    </div>
                                </>
                            )}

                            <ProductDetailPrice productPrice={product.price} />

                            <ProductDetailAvailability
                                availability={product.availability}
                                availableStoresCount={product.availableStoresCount}
                                isInquiryType={product.isInquiryType}
                                isSellingDenied={product.isSellingDenied}
                                storeAvailabilities={product.storeAvailabilities}
                            />

                            <WatchDogButton
                                availability={product.availability}
                                className="self-start"
                                isInquiryType={product.isInquiryType}
                                productIsSellingDenied={product.isSellingDenied}
                                productName={product.name}
                                productUuid={product.uuid}
                            />

                            <DeferredProductDetailAddToCart product={product} />

                            <DeferredComparisonAndWishlistButtons product={product} />
                        </div>
                        {product.promotionBuyQuantity !== null && product.promotionFreeQuantity !== null && (
                            <div className="bg-background-more flex flex-col gap-4 rounded-xl p-3 sm:p-6">
                                <div className="text-text-accent text-sm font-semibold">
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
                            <section>
                                <h2 className="h5 mb-3">{t('Recommended for you')}</h2>
                                {recommendedProductsContent}
                            </section>
                        )}
                    />
                )}

                <DeferredProductDetailAccessories accessories={product.accessories} />

                <DeferredLastVisitedProducts currentProductCatnum={product.catalogNumber} />
            </VerticalStack>
        </>
    );
};
