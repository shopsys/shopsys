import { ResultProductsStyled } from './ResultProducts.style';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC } from 'react';
import { ListedProductType } from 'types/product';

type ResultProductsProps = {
    products: ListedProductType[];
    areProductsShowed: boolean;
    noProductsFound: boolean;
    fetching: boolean;
};

export const ResultProducts: FC<ResultProductsProps> = ({ areProductsShowed, noProductsFound, products, fetching }) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            {areProductsShowed && <ProductsList products={products} gtmListName="search result" fetching={fetching} />}
            {!areProductsShowed && !noProductsFound && (
                <ResultProductsStyled>
                    <div>
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
                    </div>
                </ResultProductsStyled>
            )}

            {noProductsFound && (
                <ResultProductsStyled>
                    <div>
                        <strong>{t('No products matched your search')}</strong>
                    </div>
                </ResultProductsStyled>
            )}
        </>
    );
};
