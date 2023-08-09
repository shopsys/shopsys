import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';

type ResultProductsProps = {
    products: ListedProductFragmentApi[];
    areProductsShowed: boolean;
    noProductsFound: boolean;
    fetching: boolean;
    loadMoreFetching: boolean;
};

export const ResultProducts: FC<ResultProductsProps> = ({
    areProductsShowed,
    noProductsFound,
    products,
    fetching,
    loadMoreFetching,
}) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            {areProductsShowed && (
                <ProductsList
                    products={products}
                    gtmProductListName={GtmProductListNameType.search_results}
                    fetching={fetching}
                    loadMoreFetching={loadMoreFetching}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                />
            )}
            {!areProductsShowed && !noProductsFound && (
                <div className="p-12 text-center">
                    <div className="mb-5">
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
                    </div>
                </div>
            )}
            {noProductsFound && (
                <div className="p-12 text-center">
                    <div className="mb-5">
                        <strong>{t('No products matched your search')}</strong>
                    </div>
                </div>
            )}
        </>
    );
};
