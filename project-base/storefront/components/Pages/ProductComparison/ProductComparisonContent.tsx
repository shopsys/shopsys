import { ProductComparisonBody } from './ProductComparisonBody';
import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ProductComparisonHead } from './ProductComparisonHead';
import { ProductComparisonHeadSticky } from './ProductComparisonHeadSticky';
import { ArrowIcon } from 'components/Basic/Icon/IconsSvg';
import { Heading } from 'components/Basic/Heading/Heading';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { canUseDom } from 'helpers/canUseDom';
import { useComparisonTable } from 'hooks/comparison/useComparisonTable';
import useTranslation from 'next-translate/useTranslation';
import { useEffect, useMemo } from 'react';
import { twJoin } from 'tailwind-merge';
import { twMergeCustom } from 'helpers/twMerge';

type ProductComparisonContentProps = {
    productsCompare: ComparedProductFragmentApi[];
};

export const ProductComparisonContent: FC<ProductComparisonContentProps> = ({ productsCompare }) => {
    const { t } = useTranslation();
    const {
        isArrowLeftActive,
        isArrowRightActive,
        isArrowLeftShowed,
        isArrowRightShowed,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableMarginLeft,
    } = useComparisonTable(productsCompare.length);

    const getParametersDataState = useMemo(() => {
        const parametersData: { name: string; values: string[] }[] = [];
        productsCompare.forEach((product) => {
            product.parameters.forEach((parameter) => {
                const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

                if (indexOfParameter === -1) {
                    parametersData.push({ name: parameter.name, values: [] });
                }
            });
        });

        productsCompare.forEach((product, productIndex) => {
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
    }, [productsCompare]);

    useEffect(() => {
        if (typeof window !== 'undefined') {
            if (canUseDom()) {
                calcMaxMarginLeft();
            }
        }
    }, [calcMaxMarginLeft]);

    return (
        <>
            <div className="mb-8 flex items-end">
                <Heading type="h1" className="mb-0 w-full lg:w-auto lg:flex-1">
                    {t('Product comparison')}&nbsp;({productsCompare.length})
                </Heading>
            </div>
            <ProductComparisonButtonRemoveAll displayMobile />
            <div className="relative mb-24 overflow-hidden" id="js-table-compare-wrap">
                <div className="mb-1 flex justify-end gap-3">
                    <ContentArrow
                        isActive={isArrowLeftActive}
                        isShowed={isArrowLeftShowed}
                        onClick={() => handleSlideLeft()}
                    />
                    <ContentArrow
                        isActive={isArrowRightActive}
                        isShowed={isArrowRightShowed}
                        onClick={() => handleSlideRight()}
                        isRight
                    />
                </div>
                <ProductComparisonHeadSticky productsCompare={productsCompare} tableMarginLeft={tableMarginLeft} />
                <div>
                    <table
                        className="table-fixed border-collapse transition-all"
                        style={{ marginLeft: -tableMarginLeft }}
                        id="js-table-compare"
                    >
                        <ProductComparisonHead productsCompare={productsCompare} />
                        <ProductComparisonBody
                            productsCompare={productsCompare}
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
        className={twMergeCustom(
            'absolute right-0 top-40 z-[2] h-10 w-10 cursor-pointer items-center justify-center rounded border border-greenVeryLight bg-greyVeryLight transition-colors vl:static',
            isActive ? 'hover:bg-greyLight' : 'cursor-default border border-greyLight bg-white',

            !isRight && 'right-auto left-0',
            isShowed ? 'flex' : 'hidden',
        )}
        disabled={!isActive}
        onClick={onClick}
    >
        <ArrowIcon
            className={twJoin('w-5 text-dark', isRight ? '-rotate-90' : 'rotate-90', !isActive && 'text-greyLight')}
        />
    </button>
);
