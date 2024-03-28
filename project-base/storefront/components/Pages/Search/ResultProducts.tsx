import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import Trans from 'next-translate/Trans';
import useTranslation from 'next-translate/useTranslation';

type ResultProductsProps = {
    products: TypeListedProductFragment[];
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
    const { t } = useTranslation();

    return (
        <>
            {areProductsShowed && (
                <ProductsList
                    fetching={fetching}
                    gtmMessageOrigin={GtmMessageOriginType.other}
                    gtmProductListName={GtmProductListNameType.search_results}
                    loadMoreFetching={loadMoreFetching}
                    products={products}
                />
            )}
            {!areProductsShowed && !noProductsFound && (
                <div className="p-12 text-center">
                    <div className="mb-5">
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans components={{ 0: <br /> }} i18nKey="ProductsNoResults" />
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
