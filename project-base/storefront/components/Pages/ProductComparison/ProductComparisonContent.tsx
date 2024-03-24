import { ProductComparisonBody } from './ProductComparisonBody';
import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ProductComparisonHead } from './ProductComparisonHead';
import { ProductComparisonHeadSticky } from './ProductComparisonHeadSticky';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { ProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { twMergeCustom } from 'helpers/twMerge';
import { useComparisonTable } from 'hooks/productLists/comparison/useComparisonTable';
import { useEffect, useMemo } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductComparisonContentProps = {
    comparedProducts: ProductInProductListFragment[];
};

export const ProductComparisonContent: FC<ProductComparisonContentProps> = ({ comparedProducts }) => {
    const {
        isArrowLeftActive,
        isArrowRightActive,
        isArrowLeftShowed,
        isArrowRightShowed,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableMarginLeft,
    } = useComparisonTable(comparedProducts.length);

    const getParametersDataState = useMemo(() => {
        const parametersData: { name: string; values: string[] }[] = [];
        comparedProducts.forEach((product) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                if (indexOfParameter === -1) {
                    parametersData.push({ name: parameter.name, values: [] });
                }
            });
        });

        comparedProducts.forEach((product, productIndex) => {
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
    }, [comparedProducts]);

    useEffect(() => {
        calcMaxMarginLeft();
    }, [calcMaxMarginLeft]);

    return (
        <>
            <ProductComparisonButtonRemoveAll displayMobile />

            <div className="relative mb-24 overflow-hidden" id="js-table-compare-wrap">
                <div className="mb-1 flex justify-end gap-3">
                    <ContentArrow
                        isActive={isArrowLeftActive}
                        isShowed={isArrowLeftShowed}
                        onClick={() => handleSlideLeft()}
                    />
                    <ContentArrow
                        isRight
                        isActive={isArrowRightActive}
                        isShowed={isArrowRightShowed}
                        onClick={() => handleSlideRight()}
                    />
                </div>

                <ProductComparisonHeadSticky comparedProducts={comparedProducts} tableMarginLeft={tableMarginLeft} />

                <div>
                    <table
                        className="table-fixed border-collapse transition-all"
                        id="js-table-compare"
                        style={{ marginLeft: -tableMarginLeft }}
                    >
                        <ProductComparisonHead comparedProducts={comparedProducts} />
                        <ProductComparisonBody
                            comparedProducts={comparedProducts}
                            parametersDataState={getParametersDataState}
                        />
                    </table>
                </div>
            </div>
        </>
    );
};

type ContentArrowProps = { onClick: () => void; isActive: boolean; isRight?: boolean; isShowed?: boolean };

const ContentArrow: FC<ContentArrowProps> = ({ isActive, isRight, isShowed, onClick }) => (
    <button
        disabled={!isActive}
        className={twMergeCustom(
            'absolute right-0 top-40 z-[2] h-10 w-10 cursor-pointer items-center justify-center rounded border border-greenVeryLight bg-greyVeryLight transition-colors vl:static',
            isActive ? 'hover:bg-greyLight' : 'cursor-default border border-greyLight bg-white',

            !isRight && 'right-auto left-0',
            isShowed ? 'flex' : 'hidden',
        )}
        onClick={onClick}
    >
        <ArrowIcon
            className={twJoin('w-5 text-dark', isRight ? '-rotate-90' : 'rotate-90', !isActive && 'text-greyLight')}
        />
    </button>
);
