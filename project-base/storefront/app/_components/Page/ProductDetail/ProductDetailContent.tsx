import { ProductCompareButton } from 'app/_components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'app/_components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { WatchDogButton } from 'app/_components/Blocks/Product/Watchdog/WatchDogButton';
import { ProductDetailAddToCart } from 'app/_components/Page/ProductDetail/ProductDetailAddToCart';
import { ProductDetailAvailability } from 'app/_components/Page/ProductDetail/ProductDetailAvailability';
import { ProductDetailGallery } from 'app/_components/Page/ProductDetail/ProductDetailGallery';
import { ProductDetailInfo } from 'app/_components/Page/ProductDetail/ProductDetailInfo';
import { ProductDetailPrice } from 'app/_components/Page/ProductDetail/ProductDetailPrice';
import { ProductDetailTabs } from 'app/_components/Page/ProductDetail/ProductDetailTabs/ProductDetailTabs';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.ssr';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
};

export function ProductDetailContent({ product }: ProductDetailContentProps) {
    // const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    // useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    // useLastVisitedProductView(product.catalogNumber);
    // useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
        <>
            <section className="flex flex-col flex-wrap gap-6 lg:flex-row">
                <ProductDetailGallery
                    categoryName={product.categories[0]?.name}
                    flags={product.flags}
                    images={product.images}
                    percentageDiscount={product.price.percentageDiscount}
                    productName={product.name}
                    videoIds={product.productVideos}
                />

                <div className="flex w-full flex-1 flex-col gap-4">
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
                            productName={product.name}
                            storeAvailabilities={product.storeAvailabilities}
                        />

                        <WatchDogButton
                            availability={product.availability}
                            className="self-start"
                            isInquiryType={product.isInquiryType}
                            productIsSellingDenied={product.isSellingDenied}
                            productUuid={product.uuid}
                        />

                        <ProductDetailAddToCart product={product} />

                        <div className="flex flex-wrap gap-x-4">
                            <ProductWishlistButton isWithText productUuid={product.uuid} />
                            <ProductCompareButton isWithText productUuid={product.uuid} />
                        </div>
                    </div>
                </div>
            </section>

            <ProductDetailTabs
                description={product.description}
                files={product.files}
                parameters={product.parameters}
                relatedProducts={product.relatedProducts}
            />
        </>
    );
}
