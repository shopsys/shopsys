import { ArrowSecondaryIcon } from 'components/Basic/Icon/ArrowSecondaryIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useEffect } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useComparisonTable } from 'utils/productLists/comparison/useComparisonTable';
import { ProductComparisonBody } from './ProductComparisonBody';
import { PRODUCT_COMPARISON_END_TRIGGER_ID, ProductComparisonHead } from './ProductComparisonHead';
import { ProductComparisonHeadSticky } from './ProductComparisonHeadSticky';

type ProductComparisonContentProps = {
    comparedProducts: TypeProductInProductListFragment[];
};

const getParametersData = (comparedProducts: TypeProductInProductListFragment[]) => {
    const parametersData: { name: string; unit: string | undefined; values: string[] }[] = [];
    comparedProducts.forEach((product) => {
        product.parameters.forEach((parameter) => {
            const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

            if (indexOfParameter === -1) {
                parametersData.push({ name: parameter.name, unit: parameter.unit?.name, values: [] });
            }
        });
    });

    comparedProducts.forEach((product, productIndex) => {
        product.parameters.forEach((parameter) => {
            const indexOfParameter = parametersData.findIndex((item) => item.name === parameter.name);

            parametersData[indexOfParameter].values.push(parameter.values[0].text);
        });

        for (let i = 0; i < parametersData.length; i++) {
            if (parametersData[i].values[productIndex] === undefined) {
                parametersData[i].values.push('-');
            }
        }
    });

    return parametersData;
};

export const ProductComparisonContent: FC<ProductComparisonContentProps> = ({ comparedProducts }) => {
    const {
        isArrowLeftActive,
        isArrowRightActive,
        shouldShowArrows,
        handleSlideLeft,
        handleSlideRight,
        calcMaxMarginLeft,
        tableFirstColumnWidth,
        tableMarginLeft,
    } = useComparisonTable(comparedProducts.length);

    const parametersDataState = getParametersData(comparedProducts);

    useEffect(() => {
        calcMaxMarginLeft();
    }, [comparedProducts, calcMaxMarginLeft]);

    return (
        <div className="relative mb-24 overflow-hidden" id={PRODUCT_COMPARISON_END_TRIGGER_ID}>
            {shouldShowArrows && (
                <div className="mb-4 flex justify-end gap-2">
                    <ContentArrow isActive={isArrowLeftActive} onClick={() => handleSlideLeft()} />
                    <ContentArrow isRight isActive={isArrowRightActive} onClick={() => handleSlideRight()} />
                </div>
            )}

            <ProductComparisonHeadSticky
                comparedProducts={comparedProducts}
                tableFirstColumnWidth={tableFirstColumnWidth}
                tableMarginLeft={tableMarginLeft}
            />

            <div>
                <table
                    className="table-fixed border-collapse transition-all"
                    id="js-table-compare"
                    style={{ marginLeft: -tableMarginLeft }}
                >
                    <ProductComparisonHead comparedProducts={comparedProducts} />
                    <ProductComparisonBody
                        comparedProducts={comparedProducts}
                        parametersDataState={parametersDataState}
                    />
                </table>
            </div>
        </div>
    );
};

type ContentArrowProps = { onClick: () => void; isActive: boolean; isRight?: boolean };

const ContentArrow: FC<ContentArrowProps> = ({ isActive, isRight, onClick }) => {
    const { t } = useTranslation();

    return (
        <IconButton
            Icon={ArrowSecondaryIcon}
            ariaLabel={
                isRight
                    ? t('Show next product in comparison', { ns: 'accessibility' })
                    : t('Show previous product in comparison', { ns: 'accessibility' })
            }
            disabled={!isActive}
            iconClassName={isRight ? '-rotate-90' : 'rotate-90'}
            shape="rounded"
            size="large"
            tabIndex={isActive ? 0 : -1}
            title={isRight ? t('Next product') : t('Previous product')}
            variant="ghost"
            onClick={onClick}
        />
    );
};
