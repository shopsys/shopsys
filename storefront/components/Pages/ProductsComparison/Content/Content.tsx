import {
    ContentArrowIconStyled,
    ContentArrowsStyled,
    ContentArrowStyled,
    ContentProductsTableStyled,
    ContentProductsTableWrapperStyled,
    ContentTopHeadingStyled,
    ContentTopStyled,
} from './Content.style';
import clsx from 'clsx';
import Body from 'components/Pages/ProductsComparison/Body/Body';
import { ButtonRemoveAll } from 'components/Pages/ProductsComparison/ButtonRemoveAll/ButtonRemoveAll';
import Head from 'components/Pages/ProductsComparison/Head/Head';
import { HeadSticky } from 'components/Pages/ProductsComparison/HeadSticky/HeadSticky';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useHandleCompareTable } from 'hooks/product/useHandleCompareTable';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useEffect, useMemo } from 'react';

type ContentProps = {
    productsCompare: ComparedProductFragmentApi[];
};

export const Content: FC<ContentProps> = (props) => {
    const t = useTypedTranslationFunction();
    const {
        isArrowLeftActive,
        isArrowRightActive,
        isArrowLeftShowed,
        isArrowRightShowed,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableMarginLeft,
    } = useHandleCompareTable(props.productsCompare.length);

    const getParametersDataState = useMemo(() => {
        const parametersData: { name: string; values: string[] }[] = [];
        props.productsCompare.forEach((product) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                if (indexOfParameter === -1) {
                    parametersData.push({ name: parameter.name, values: [] });
                }
            });
        });

        props.productsCompare.forEach((product, productIndex) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                parametersData[indexOfParameter].values.push(parameter.values[0].text);
            });

            for (let i = 0; i < parametersData.length; i++) {
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                if (parametersData[i].values[productIndex] === undefined) {
                    parametersData[i].values.push('-');
                }
            }
        });

        return parametersData;
    }, [props.productsCompare]);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            if (canUseDom()) {
                calcMaxMarginLeft();
            }
        }
    }, [calcMaxMarginLeft]);

    return (
        <>
            <ContentTopStyled>
                <ContentTopHeadingStyled type="h1">
                    {t('Product comparison')}&nbsp;({props.productsCompare.length})
                </ContentTopHeadingStyled>
            </ContentTopStyled>
            <ButtonRemoveAll displayMobile />
            <ContentProductsTableWrapperStyled id="js-table-compare-wrap">
                <ContentArrowsStyled>
                    <ContentArrowStyled
                        className={clsx(!isArrowLeftActive && 'isInactive', isArrowLeftShowed && 'isShowed')}
                        onClick={() => handleSlideLeft()}
                    >
                        <ContentArrowIconStyled iconType="icon" icon="ArrowThin" />
                    </ContentArrowStyled>
                    <ContentArrowStyled
                        className={clsx(!isArrowRightActive && 'isInactive', isArrowRightShowed && 'isShowed')}
                        onClick={() => handleSlideRight()}
                    >
                        <ContentArrowIconStyled className="isRightArrow" iconType="icon" icon="ArrowThin" />
                    </ContentArrowStyled>
                </ContentArrowsStyled>
                <HeadSticky productsCompare={props.productsCompare} tableMarginLeft={tableMarginLeft} />
                <div>
                    <ContentProductsTableStyled style={{ marginLeft: -tableMarginLeft }} id="js-table-compare">
                        <Head productsCompare={props.productsCompare} />
                        <Body productsCompare={props.productsCompare} parametersDataState={getParametersDataState} />
                    </ContentProductsTableStyled>
                </div>
            </ContentProductsTableWrapperStyled>
        </>
    );
};
