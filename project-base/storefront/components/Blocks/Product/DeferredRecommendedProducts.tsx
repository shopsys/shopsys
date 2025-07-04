import { ProductsSlider } from './ProductsSlider';
import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { Webline } from 'components/Layout/Webline/Webline';
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

const ProductsSliderPlaceholder = dynamic(() =>
    import('./ProductsSliderPlaceholder').then((component) => component.ProductsSliderPlaceholder),
);

export type DeferredRecommendedProductsProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
    render: (input: JSX.Element) => ReactElement<any, any> | null;
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

    useEffect(() => {
        setIsClientMounted(true);
    }, []);

    const shouldShowSkeleton =
        (isClientMounted && areRecommendedProductsFetching) ||
        (isBasketPopup && !recommendedProductsData?.recommendedProducts.length && areRecommendedProductsFetching);

    if (shouldShowSkeleton) {
        return (
            <Webline>
                <SkeletonModuleProductSlider isWithSimpleCards={isBasketPopup} />
            </Webline>
        );
    }

    if (!recommendedProductsData?.recommendedProducts.length) {
        return null;
    }

    const productItemStyleProps = {
        size: recommendationType === TypeRecommendationType.BasketPopup ? ('medium' as const) : ('large' as const),
        visibleItemsConfig:
            recommendationType === TypeRecommendationType.BasketPopup
                ? { price: true, addToCart: true, flags: true, storeAvailability: true }
                : undefined,
    };

    return (
        <Webline>
            {shouldRender
                ? render(
                      <ProductsSlider
                          isLuigisEnabled
                          ariaAnchorName="product-slider-recommended"
                          gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                          productItemProps={productItemStyleProps}
                          products={recommendedProductsData.recommendedProducts}
                      />,
                  )
                : render(
                      <ProductsSliderPlaceholder
                          products={recommendedProductsData.recommendedProducts}
                          size={productItemStyleProps.size}
                          visibleItemsConfig={productItemStyleProps.visibleItemsConfig}
                      />,
                  )}
        </Webline>
    );
};
