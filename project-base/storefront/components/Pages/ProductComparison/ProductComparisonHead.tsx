import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { CSSProperties } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';

export const PRODUCT_COMPARISON_STICKY_TRIGGER_ID = 'js-product-comparison-sticky-trigger';
export const PRODUCT_COMPARISON_END_TRIGGER_ID = 'js-table-compare-wrap';

type ProductComparisonHeadProps = {
    comparedProducts: TypeProductInProductListFragment[];
    allProducts: TypeProductInProductListFragment[];
    onRemove: (product: TypeProductInProductListFragment) => void;
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({ comparedProducts, allProducts, onRemove }) => {
    const { t } = useTranslation();
    return (
        <thead className="block md:table-header-group">
            <tr className="sr-only">
                <th scope="col">{t('Parameters')}</th>
                {comparedProducts.map((product, index) => (
                    <th key={product.uuid} id={`comparison-product-${index}`} scope="col">
                        {product.fullName}
                    </th>
                ))}
            </tr>
            <tr className="block md:table-row">
                <td className="block p-0 md:table-cell" colSpan={comparedProducts.length + 1}>
                    <div
                        className="grid grid-flow-col grid-cols-2 grid-rows-[auto_auto_auto_auto_auto_auto_auto_auto] md:grid-cols-[12rem_repeat(var(--comparison-products),minmax(0,1fr))]"
                        id="js-table-compare-head"
                        style={{ '--comparison-products': comparedProducts.length } as CSSProperties}
                    >
                        <div className="row-span-8 hidden w-48 bg-table-bg-default md:sticky md:left-0 md:z-above md:block" />
                        {comparedProducts.map((product, index) => (
                            <ProductComparisonHeadItem
                                key={product.uuid}
                                columnIndex={index}
                                listIndex={allProducts.findIndex((item) => item.uuid === product.uuid)}
                                product={product}
                                stickyTriggerId={index === 0 ? PRODUCT_COMPARISON_STICKY_TRIGGER_ID : undefined}
                                toggleProductInComparison={() => onRemove(product)}
                            />
                        ))}
                    </div>
                </td>
            </tr>
        </thead>
    );
};
