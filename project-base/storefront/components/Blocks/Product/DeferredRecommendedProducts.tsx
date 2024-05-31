import { ProductsSlider } from './ProductsSlider';
import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecommendedProductsQuery } from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import dynamic from 'next/dynamic';
import { ReactElement } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';
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

    if (areRecommendedProductsFetching) {
        return render(
            <SkeletonModuleProductSlider
                isWithSimpleCards={recommendationType === TypeRecommendationType.BasketPopup}
            />,
        );
    }

    if (!recommendedProductsData?.recommendedProducts.length) {
        return null;
    }

    return shouldRender
        ? render(
              <ProductsSlider
                  gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                  isWithSimpleCards={recommendationType === TypeRecommendationType.BasketPopup}
                  products={recommendedProductsData.recommendedProducts}
              />,
          )
        : render(
              <ProductsSliderPlaceholder
                  isWithSimpleCards={recommendationType === TypeRecommendationType.BasketPopup}
                  products={recommendedProductsData.recommendedProducts}
              />,
          );
};
