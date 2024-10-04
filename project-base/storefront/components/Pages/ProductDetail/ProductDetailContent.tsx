import { DeferredComparisonAndWishlistButtons } from './ComparisonAndWishlistButtons/DeferredComparisonAndWishlistButtons';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { DeferredProductDetailAddToCart } from './ProductDetailAddToCart/DeferredProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailPrefix, ProductDetailHeading } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { DeferredRecommendedProducts } from 'components/Blocks/Product/DeferredRecommendedProducts';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/lastVisitedProductsUtils';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { isPriceVisible } from 'utils/mappers/price';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
    isProductDetailFetching: boolean;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, isProductDetailFetching }) => {
    const { t } = useTranslation();
    const router = useRouter();

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
                        <div className="flex flex-col gap-1">
                            {product.namePrefix && <ProductDetailPrefix>{product.namePrefix}</ProductDetailPrefix>}

                            <ProductDetailHeading>
                                {product.name} {product.nameSuffix}
                            </ProductDetailHeading>

                            <div className="flex gap-4 text-[13px]">
                                {product.brand && (
                                    <div>
                                        <span>{t('Brand')}: </span>
                                        <ExtendedNextLink href={product.brand.slug} type="brand">
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
                                <div className="font-secondary text-2xl font-bold text-price">
                                    {formatPrice(product.price.priceWithVat)}
                                </div>
                            )}

                            <ProductDetailAvailability product={product} />

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
