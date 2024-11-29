import { DeferredComparisonAndWishlistButtons } from './ComparisonAndWishlistButtons/DeferredComparisonAndWishlistButtons';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { DeferredProductDetailAddToCart } from './ProductDetailAddToCart/DeferredProductDetailAddToCart';
import { ProductDetailPrefix, ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { ProductAvailability } from 'components/Blocks/Product/ProductAvailability';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { Popup } from 'components/Layout/Popup/Popup';
import { Webline } from 'components/Layout/Webline/Webline';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum, TypeRecommendationType } from 'graphql/types';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { twMergeCustom } from 'utils/twMerge';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, isProductDetailFetching }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const { isLuigisBoxActive } = useDomainConfig();
    const formatPrice = useFormatPrice();

    const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            <ProductMetadata product={product} />

            <Webline className="flex flex-col gap-8">
                <div className="flex flex-col flex-wrap gap-6 lg:flex-row">
                    <ProductDetailGallery
                        flags={product.flags}
                        images={product.images}
                        productName={product.name}
                        videoIds={product.productVideos}
                    />

                    <div className="flex w-full flex-1 flex-col gap-4">
                        <div className="flex flex-col">
                            {product.namePrefix && <ProductDetailPrefix>{product.namePrefix}</ProductDetailPrefix>}

                            <ProductDetailHeading>
                                {product.name} {product.nameSuffix}
                            </ProductDetailHeading>

                            <div className="flex items-center gap-5 text-sm">
                                {product.brand && (
                                    <div>
                                        <span>{t('Brand')}: </span>
                                        <ExtendedNextLink className="text-sm" href={product.brand.slug} type="brand">
                                            {product.brand.name}
                                        </ExtendedNextLink>
                                    </div>
                                )}

                                <div>
                                    {t('Code')}: {product.catalogNumber}
                                </div>
                            </div>
                        </div>

                        {product.shortDescription && <div className="text-sm">{product.shortDescription}</div>}

                        {!!product.usps.length && <ProductDetailUsps usps={product.usps} />}

                        <div className="flex flex-col gap-4 rounded-xl bg-backgroundMore p-3 sm:p-6">
                            {isPriceVisible(product.price.priceWithVat) && (
                                <>
                                    {!!product.price.percentageDiscount && (
                                        <>
                                            <div className="text-3xl font-bold text-textError">
                                                {formatPrice(product.price.priceWithVat)}
                                            </div>
                                            <div>Discount {product.price.percentageDiscount}%</div>
                                        </>
                                    )}

                                    <div
                                        className={twMergeCustom(
                                            'font-secondary text-2xl font-bold text-price',
                                            !!product.price.percentageDiscount && 'line-through',
                                        )}
                                    >
                                        {formatPrice(product.price.basicPrice.priceWithVat)}
                                    </div>
                                </>
                            )}

                            {!product.isSellingDenied && (
                                <ProductAvailability
                                    availability={product.availability}
                                    availableStoresCount={product.availableStoresCount}
                                    isInquiryType={product.isInquiryType}
                                    className={twJoin(
                                        'mr-1 flex items-center font-secondary',
                                        product.availability.status === TypeAvailabilityStatusEnum.InStock &&
                                            'cursor-pointer',
                                    )}
                                    onClick={() =>
                                        product.availability.status === TypeAvailabilityStatusEnum.InStock &&
                                        updatePortalContent(
                                            <Popup>
                                                <ProductDetailAvailabilityList
                                                    storeAvailabilities={product.storeAvailabilities}
                                                />
                                            </Popup>,
                                        )
                                    }
                                />
                            )}

                            <WatchDogButton
                                availability={product.availability}
                                className="self-start"
                                isInquiryType={product.isInquiryType}
                                productIsSellingDenied={product.isSellingDenied}
                                productUuid={product.uuid}
                            />

                            <DeferredProductDetailAddToCart product={product} />

                            <DeferredComparisonAndWishlistButtons product={product} />
                        </div>
                    </div>
                </div>

                <ProductDetailTabs
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
                            <div>
                                <div className="text-xl font-bold">{t('Recommended for you')}</div>
                                {recommendedProductsContent}
                            </div>
                        )}
                    />
                )}

                <DeferredProductDetailAccessories accessories={product.accessories} />
            </Webline>
        </>
    );
};
