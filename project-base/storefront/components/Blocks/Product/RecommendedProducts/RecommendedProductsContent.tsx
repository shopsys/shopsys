import { getRecommendedProductsQuery } from 'app/_queries/getRecommendedProductsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeRecommendationType } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getServerT } from 'utils/getServerTranslation';
import { getRecommenderClientIdentifier } from 'utils/recommender/getRecommenderClientIdentifier';

export type RecommendedProductsContentProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
};

export const RecommendedProductsContent: FC<RecommendedProductsContentProps> = async ({
    recommendationType,
    itemUuids = [],
}) => {
    const [t] = await Promise.all([getServerT()]);
    const { userIdentifier } = getCookieStoreStateFromServer();

    // TODO: new functionality for recommended products identifier
    // const { pathname } = useRouter();
    const pathname = '/products/[productSlug]';

    const { data: recommendedProductsData } = await getRecommendedProductsQuery({
        userIdentifier,
        recommendationType,
        recommenderClientIdentifier: getRecommenderClientIdentifier(pathname),
        limit: 10,
        itemUuids,
    });

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
        <>
            <h5 className="mb-4">{t('Recommended for you')}</h5>

            <ProductsSlider
                gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                productItemProps={productItemStyleProps}
                products={recommendedProductsData.recommendedProducts}
            />
        </>
    );
};
