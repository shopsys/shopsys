import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecommendedProductsQuery } from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { ReactElement, useEffect, useState } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { getRecommenderClientIdentifier } from 'utils/recommender/getRecommenderClientIdentifier';
import { useDeferredRender } from 'utils/useDeferredRender';
import { VISIBLE_SLIDER_ITEMS_BASKET_POPUP } from './ProductsSlider';
import { ProductsSliderPlaceholder } from './ProductsSliderPlaceholder';

const ProductsSlider = dynamic(() => import('./ProductsSlider').then((component) => component.ProductsSlider), {
    ssr: false,
});

type DeferredRecommendedProductsProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
    render: (input: ReactElement) => ReactElement<any, any> | null;
};

export const DeferredRecommendedProducts: FC<DeferredRecommendedProductsProps> = ({
    recommendationType,
    render,
    itemUuids = [],
}) => {
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const { isLuigisBoxActive } = useDomainConfig();
    const { pathname } = useRouter();
    const [isClientMounted, setIsClientMounted] = useState(false);
    const [{ data: recommendedProductsData, fetching: areRecommendedProductsFetching }] = useRecommendedProductsQuery({
        variables: {
            itemUuids,
            userIdentifier,
            recommendationType,
            recommenderClientIdentifier: getRecommenderClientIdentifier(pathname),
            limit: 10,
        },
        pause: !isLuigisBoxActive,
    });

    const shouldRender = useDeferredRender('recommended_products');
    const isBasketPopup = recommendationType === TypeRecommendationType.BasketPopup;
    const sliderVariant = isBasketPopup ? ('basketPopup' as const) : undefined;
    const visibleSliderItems = isBasketPopup ? VISIBLE_SLIDER_ITEMS_BASKET_POPUP : undefined;
    const productItemStyleProps = {
        size: isBasketPopup ? ('medium' as const) : ('large' as const),
        visibleItemsConfig: isBasketPopup
            ? { price: true, addToCart: true, flags: true, storeAvailability: true }
            : undefined,
    };

    useEffect(() => {
        setIsClientMounted(true);
    }, []);

    const shouldShowSkeleton =
        (isClientMounted && areRecommendedProductsFetching) ||
        (isBasketPopup && !recommendedProductsData?.recommendedProducts.length && areRecommendedProductsFetching);

    if (shouldShowSkeleton) {
        return render(
            <SkeletonModuleProductSlider
                isHeadingHidden
                productItemProps={productItemStyleProps}
                variant={sliderVariant}
                visibleSliderItems={visibleSliderItems}
            />,
        );
    }

    if (!recommendedProductsData?.recommendedProducts.length) {
        return null;
    }

    return (
        <>
            {shouldRender
                ? render(
                      <ProductsSlider
                          isLuigisEnabled
                          ariaAnchorName="product-slider-recommended"
                          gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                          productItemProps={productItemStyleProps}
                          products={recommendedProductsData.recommendedProducts}
                          variant={sliderVariant}
                          visibleSliderItems={visibleSliderItems}
                      />,
                  )
                : render(
                      <ProductsSliderPlaceholder
                          products={recommendedProductsData.recommendedProducts}
                          size={productItemStyleProps.size}
                          variant={sliderVariant}
                          visibleItemsConfig={productItemStyleProps.visibleItemsConfig}
                          visibleSliderItems={visibleSliderItems}
                      />,
                  )}
        </>
    );
};
