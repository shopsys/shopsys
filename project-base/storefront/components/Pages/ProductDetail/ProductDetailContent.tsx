'use client';

import { ProductDetailPrefix, ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailPrice } from './ProductDetailPrice';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product }) => {
    const { t } = useTranslation();
    // const router = useRouter();
    // const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    // const { isLuigisBoxActive } = useDomainConfig();

    // const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    // useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    // useLastVisitedProductView(product.catalogNumber);
    // useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            {/* <ProductMetadata product={product} /> */}

            <Webline className="flex flex-col gap-8">
                <div className="flex flex-col flex-wrap gap-6 lg:flex-row">
                    <ProductDetailGallery
                        flags={product.flags}
                        images={product.images}
                        percentageDiscount={product.price.percentageDiscount}
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
                            <ProductDetailPrice productPrice={product.price} />

                            {/* {!product.isSellingDenied && (
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
                                            <Popup contentClassName="overflow-scroll">
                                                <ProductDetailAvailabilityList
                                                    storeAvailabilities={product.storeAvailabilities}
                                                />
                                            </Popup>,
                                        )
                                    }
                                />
                            )} */}

                            {/* <WatchDogButton
                                availability={product.availability}
                                className="self-start"
                                isInquiryType={product.isInquiryType}
                                productIsSellingDenied={product.isSellingDenied}
                                productUuid={product.uuid}
                            /> */}

                            {/* <ProductDetailAddToCart product={product} /> */}

                            {/* <DeferredComparisonAndWishlistButtons product={product} /> */}
                        </div>
                    </div>
                </div>

                <ProductDetailTabs
                    description={product.description}
                    files={product.files}
                    parameters={product.parameters}
                    relatedProducts={product.relatedProducts}
                />

                {/* {isLuigisBoxActive && (
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
                )} */}

                {/* <ProductDetailAccessories accessories={product.accessories} /> */}
                {/* <DeferredProductDetailAccessories accessories={product.accessories} /> */}
            </Webline>
        </>
    );
};
