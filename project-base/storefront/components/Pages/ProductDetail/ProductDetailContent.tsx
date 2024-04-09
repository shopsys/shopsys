import { DeferredComparisonAndWishlistButtons } from './ComparisonAndWishlistButtons/DeferredComparisonAndWishlistButtons';
import { DeferredProductDetailAccessories } from './ProductDetailAccessories/DeferredProductDetailAccessories';
import { DeferredProductDetailAddToCart } from './ProductDetailAddToCart/DeferredProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductDetailPrefix, ProductDetailHeading, ProductDetailCode } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs/ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { useLastVisitedProductView } from 'components/Blocks/Product/LastVisitedProducts/utils';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeRecommendationType } from 'graphql/types';
import { useGtmProductDetailViewEvent } from 'gtm/utils/pageViewEvents/useGtmProductDetailViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { useRef } from 'react';
import { useFormatPrice } from 'utils/formatting/useFormatPrice';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

const RecommendedProducts = dynamic(() =>
    import('components/Blocks/Product/RecommendedProducts').then((component) => component.RecommendedProducts),
);

type ProductDetailContentProps = {
    product: TypeProductDetailFragment;
    fetching: boolean;
};

export const ProductDetailContent: FC<ProductDetailContentProps> = ({ product, fetching }) => {
    const { t } = useTranslation();
    const scrollTarget = useRef<HTMLUListElement>(null);
    const router = useRouter();

    const { isLuigisBoxActive } = useDomainConfig();
    const formatPrice = useFormatPrice();

    useLastVisitedProductView(product.catalogNumber);
    useGtmProductDetailViewEvent(product, getUrlWithoutGetParameters(router.asPath), fetching);

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

                    <div className="flex flex-1 flex-col gap-4">
                        <div className="flex flex-col gap-1">
                            {product.namePrefix && <ProductDetailPrefix>{product.namePrefix}</ProductDetailPrefix>}

                            <ProductDetailHeading>
                                {product.name} {product.nameSuffix}
                            </ProductDetailHeading>

                            <ProductDetailCode>
                                {t('Code')}: {product.catalogNumber}
                            </ProductDetailCode>
                        </div>

                        {product.shortDescription && <div>{product.shortDescription}</div>}

                        {product.usps.length > 0 && <ProductDetailUsps usps={product.usps} />}

                        <div className="flex flex-col gap-4 rounded bg-blueLight p-3">
                            <div className="text-2xl font-bold text-primary">
                                {formatPrice(product.price.priceWithVat)}
                            </div>
                            <DeferredProductDetailAddToCart product={product} />
                        </div>

                        <ProductDetailAvailability product={product} scrollTarget={scrollTarget} />

                        <DeferredComparisonAndWishlistButtons product={product} />
                    </div>
                </div>

                <ProductDetailTabs
                    description={product.description}
                    parameters={product.parameters}
                    relatedProducts={product.relatedProducts}
                />

                <ProductDetailAvailabilityList ref={scrollTarget} storeAvailabilities={product.storeAvailabilities} />
                {isLuigisBoxActive && (
                    <RecommendedProducts
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
