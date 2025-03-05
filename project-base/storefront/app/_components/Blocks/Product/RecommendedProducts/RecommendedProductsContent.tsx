import { ProductSlider } from 'app/_components/Blocks/Product/ProductSlider';
import { ProductListItem } from 'app/_components/Blocks/Product/ProductsList/ProductListItem';
import { getRecommendedProductsQuery } from 'app/_queries/getRecommendedProductsQuery';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { getRecommenderClientIdentifier } from 'app/_utils/recommender/getRecommenderClientIdentifier';
import { getTranslation } from 'app/_utils/translation/getTranslation';
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

    const productItemStyleProps = {
        size: recommendationType === TypeRecommendationType.BasketPopup ? ('medium' as const) : ('large' as const),
        visibleItemsConfig:
            recommendationType === TypeRecommendationType.BasketPopup
                ? { price: true, addToCart: true, flags: true, storeAvailability: true }
                : undefined,
    };

    return (
        <section>
            <h5 className="mb-4">{t('Recommended for you')}</h5>

            <ProductSlider totalItems={recommendedProductsData.recommendedProducts.length} variant="default">
                {recommendedProductsData.recommendedProducts.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        isShownInSlider
                        gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                        gtmProductListName={GtmProductListNameType.luigis_box_recommended_products}
                        listIndex={index}
                        product={product}
                        {...productItemStyleProps}
                    />
                ))}
            </ProductSlider>
        </section>
    );
};
