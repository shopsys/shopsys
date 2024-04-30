import { ProductsSlider } from './ProductsSlider';
import { SkeletonModuleProductSlider } from 'components/Blocks/Skeleton/SkeletonModuleProductSlider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { RecommendationTypeApi, useRecommendedProductsQueryApi } from 'graphql/generated';
import { GtmProductListNameType } from 'gtm/types/enums';
import { ReactElement } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';

type RecommendedProductsProps = {
    recommendationType: RecommendationTypeApi;
    itemUuids?: string[];
    render: (input: JSX.Element) => ReactElement<any, any> | null;
};

export const RecommendedProducts: FC<RecommendedProductsProps> = ({ recommendationType, render, itemUuids = [] }) => {
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const { isLuigisBoxActive } = useDomainConfig();
    const [{ data: recommendedProductsData, fetching: isFetching }] = useRecommendedProductsQueryApi({
        variables: {
            itemUuids,
            userIdentifier,
            recommendationType,
            limit: 10,
        },
        pause: !isLuigisBoxActive,
    });

    if (isFetching) {
        return render(
            <SkeletonModuleProductSlider
                isWithSimpleCards={recommendationType === RecommendationTypeApi.BasketPopupApi}
            />,
        );
    }

    if (!recommendedProductsData?.recommendedProducts.length) {
        return null;
    }

    return render(
        <ProductsSlider
            gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
            isWithSimpleCards={recommendationType === RecommendationTypeApi.BasketPopupApi}
            products={recommendedProductsData.recommendedProducts}
        />,
    );
};
