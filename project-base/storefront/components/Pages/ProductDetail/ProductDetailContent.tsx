import { ProductDetailAddToCart } from './ProductDetailAddToCart/ProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailInfo } from './ProductDetailInfo';
import { ProductDetailPrice } from './ProductDetailPrice';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { WatchDogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
};

export function ProductDetailContent({ product }: ProductDetailContentProps) {
    // const router = useRouter();

    // const pageViewEvent = useGtmFriendlyPageViewEvent(product);
    // useGtmPageViewEvent(pageViewEvent, isProductDetailFetching);
    // useLastVisitedProductView(product.catalogNumber);
    // useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), isProductDetailFetching);

    return (
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
                    <ProductDetailInfo
                        brand={product.brand}
                        catalogNumber={product.catalogNumber}
                        name={product.name}
                        namePrefix={product.namePrefix}
                        nameSuffix={product.nameSuffix}
                        shortDescription={product.shortDescription}
                        usps={product.usps}
                    />

                    <div className="flex flex-col gap-4 rounded-xl bg-backgroundMore p-3 sm:p-6">
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
                            productUuid={product.uuid}
                        />

                        <ProductDetailAddToCart product={product} />

                        {/* TODO: add product comparion and wishlist */}
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
        </Webline>
    );
}
