import { ProductDetailAccessories } from './ProductDetailAccessories';
import { ProductDetailAddToCart } from './ProductDetailAddToCart';
import { ProductDetailAvailability } from './ProductDetailAvailability';
import { ProductDetailAvailabilityList } from './ProductDetailAvailabilityList';
import { ProductDetailPrefix, ProductDetailHeading, ProductDetailCode } from './ProductDetailElements';
import { ProductDetailGallery } from './ProductDetailGallery';
import { ProductDetailTabs } from './ProductDetailTabs';
import { ProductDetailUsps } from './ProductDetailUsps';
import { ProductMetadata } from 'components/Basic/Head/ProductMetadata';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
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
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';

const ProductComparePopup = dynamic(() =>
    import('components/Blocks/Product/ButtonsAction/ProductComparePopup').then(
        (component) => component.ProductComparePopup,
    ),
);

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
    const { isProductInComparison, toggleProductInComparison, isPopupCompareOpen, setIsPopupCompareOpen } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();
    const { isLuigisBoxActive } = useDomainConfig();

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

                        <ProductDetailAddToCart product={product} />

                        <ProductDetailAvailability product={product} scrollTarget={scrollTarget} />

                        <div className="flex flex-col gap-y-2 gap-x-4 vl:flex-row">
                            <ProductCompareButton
                                isWithText
                                isProductInComparison={isProductInComparison(product.uuid)}
                                toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                            />
                            <ProductWishlistButton
                                isWithText
                                isProductInWishlist={isProductInWishlist(product.uuid)}
                                toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                            />
                        </div>
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

                {!!product.accessories.length && <ProductDetailAccessories accessories={product.accessories} />}
            </Webline>
            {isPopupCompareOpen && <ProductComparePopup onCloseCallback={() => setIsPopupCompareOpen(false)} />}
        </>
    );
};
