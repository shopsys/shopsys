import { ResultProductsStyled } from './ResultProducts.style';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Trans from 'next-translate/Trans';
import { FC } from 'react';
import { ListedProductType } from 'types/product';

type ResultProductsProps = {
    products: ListedProductType[];
    areProductsShowed: boolean;
    noProductsFound: boolean;
};

export const ResultProducts: FC<ResultProductsProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            {props.areProductsShowed && <ProductsList products={props.products} gtmListName="search result" />}
            {props.areProductsShowed === false && props.noProductsFound === false && (
                <ResultProductsStyled>
                    <div>
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
                    </div>
                </ResultProductsStyled>
            )}

            {props.noProductsFound && (
                <ResultProductsStyled>
                    <div>
                        <strong>{t('No products matched your search')}</strong>
                    </div>
                </ResultProductsStyled>
            )}
        </>
    );
};
