import { FC } from 'react';
import { ListedProductType } from 'types/product';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import { ResultProductsStyled } from './ResultProducts.style';
import { Trans } from 'next-i18next';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ResultProductsProps = {
    products: ListedProductType[];
    areProductsShowed: boolean;
    noProductsFound: boolean;
};

const ResultProducts: FC<ResultProductsProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <>
            {props.areProductsShowed && <ProductsList products={props.products} />}

            {props.areProductsShowed === false && props.noProductsFound === false && (
                <ResultProductsStyled>
                    <div>
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans i18nKey="ProductsNoResults">
                            We currently have no results for your exact search.
                            <br />
                            Try to be more specific, or see if you have filtered out non-existent data.
                        </Trans>
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

export default ResultProducts;
