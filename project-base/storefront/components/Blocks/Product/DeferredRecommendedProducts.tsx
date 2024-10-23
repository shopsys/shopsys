import { ProductsSlider } from './ProductsSlider';
import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecommendedProductsQuery } from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { ReactElement } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
import { useDeferredRender } from 'utils/useDeferredRender';

const ProductsSliderPlaceholder = dynamic(() =>
    import('./ProductsSliderPlaceholder').then((component) => ({
        default: component.ProductsSliderPlaceholder
    })),
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
    const [{ data: recommendedProductsData, fetching: areRecommendedProductsFetching }] = useRecommendedProductsQuery({
        variables: {
            itemUuids,
            userIdentifier,
            recommendationType,
            limit: 10,
        },
        pause: !isLuigisBoxActive,
    });
    const shouldRender = useDeferredRender('recommended_products');

    const weblineTwClasses = 'mb-6';

    if (areRecommendedProductsFetching) {
        return (
            <Webline className={weblineTwClasses}>
                <SkeletonModuleProductSlider
                    isWithSimpleCards={recommendationType === TypeRecommendationType.BasketPopup}
                />
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
        <Webline className={weblineTwClasses}>
            {shouldRender
                ? render(
                      <ProductsSlider
                          gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                          products={recommendedProductsData.recommendedProducts}
                          productItemProps={{
                              size: productItemStyleProps.size,
                              visibleItemsConfig: productItemStyleProps.visibleItemsConfig,
                          }}
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
