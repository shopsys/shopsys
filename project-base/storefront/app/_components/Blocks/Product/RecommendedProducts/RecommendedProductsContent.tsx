import { ProductsList } from 'app/_components/Blocks/Product/ProductsList/ProductsList';
import { getRecommendedProductsQuery } from 'app/_queries/getRecommendedProductsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { getRecommenderClientIdentifier } from 'app/_utils/recommender/getRecommenderClientIdentifier';
import { getTranslation } from 'app/_utils/translation/getTranslation';
// import { ProductsSlider } from 'components/Blocks/Product/ProductsSlider';
import { TypeRecommendationType } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';

export type RecommendedProductsContentProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
};

export const RecommendedProductsContent: FC<RecommendedProductsContentProps> = async ({
    recommendationType,
    itemUuids = [],
}) => {
    const [t] = await Promise.all([getTranslation()]);
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

    // TODO: SLIDER
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

            {/* <ProductsSlider
                gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                productItemProps={productItemStyleProps}
                products={productsWithListState}
            /> */}

            <ProductsList
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                products={recommendedProductsData.recommendedProducts}
            />
        </>
    );
};
