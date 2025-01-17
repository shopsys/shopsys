'use client';

import { ProductDetailAccessories } from './ProductDetailAccessories/ProductDetailAccessories';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductDetailPrefix, ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailPrice } from './ProductDetailPrice';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Popup } from 'components/Layout/Popup/Popup';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTranslation } from 'components/providers/TranslationProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product }) => {
    const { t } = useTranslation();
    // const router = useRouter();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    // const { isLuigisBoxActive } = useDomainConfig();

    // const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    // useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    // useLastVisitedProductView(product.catalogNumber);
    // useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            {/* <ProductMetadata product={product} /> */}

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

                            {/* <ProductDetailAddToCart product={product} /> */}

                            {/* <DeferredComparisonAndWishlistButtons product={product} /> */}
                        </div>
                    </div>
                </Webline>

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
                            <section>
                                <h2 className="h5 mb-3">{t('Recommended for you')}</h2>
                                {recommendedProductsContent}
                            </section>
                        )}
                    />
                )} */}

                {/* <ProductDetailAccessories accessories={product.accessories} /> */}
                {/* <DeferredProductDetailAccessories accessories={product.accessories} /> */}
            </VerticalStack>
        </>
    );
};
