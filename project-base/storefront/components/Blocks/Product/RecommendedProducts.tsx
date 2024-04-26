import { ProductsSlider } from './ProductsSlider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useRecommendedProductsQuery } from 'graphql/requests/products/queries/RecommendedProductsQuery.generated';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ReactElement } from 'react';
import { useCookiesStore } from 'store/useCookiesStore';

type RecommendedProductsProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
    render: (input: JSX.Element) => ReactElement<any, any> | null;
};

export const RecommendedProducts: FC<RecommendedProductsProps> = ({ recommendationType, render, itemUuids = [] }) => {
    const userIdentifier = useCookiesStore((store) => store.userIdentifier);
    const { isLuigisBoxActive } = useDomainConfig();
    const [{ data: recommendedProductsData }] = useRecommendedProductsQuery({
        variables: {
            itemUuids,
            userIdentifier,
            recommendationType,
            limit: 10,
        },
        pause: !isLuigisBoxActive,
    });

    if (!recommendedProductsData?.recommendedProducts.length) {
        return null;
    }

    return render(
        <ProductsSlider
            gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
            isWithSimpleCards={recommendationType === TypeRecommendationType.BasketPopup}
            products={recommendedProductsData.recommendedProducts}
        />,
    );
};
