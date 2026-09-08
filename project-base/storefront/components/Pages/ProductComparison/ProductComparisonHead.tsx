import { Reorder } from 'framer-motion';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { CSSProperties, useState } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';

export const PRODUCT_COMPARISON_STICKY_TRIGGER_ID = 'js-product-comparison-sticky-trigger';
export const PRODUCT_COMPARISON_END_TRIGGER_ID = 'js-table-compare-wrap';

type ProductComparisonHeadProps = {
    comparedProducts: TypeProductInProductListFragment[];
    allProducts: TypeProductInProductListFragment[];
    canReorder?: boolean;
    onReorderEnd?: () => void;
    onReorder?: (uuids: string[]) => void;
    onProductFocus?: (index: number) => void;
    onRemove: (product: TypeProductInProductListFragment) => void;
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({
    comparedProducts,
    allProducts,
    onRemove,
    onReorder,
    onReorderEnd,
    canReorder,
    onProductFocus,
}) => {
    const { t } = useTranslation();
    const [announcement, setAnnouncement] = useState('');
    const moveProduct = (uuid: string, direction: number) => {
        const order = comparedProducts.map((product) => product.uuid);
        const index = order.indexOf(uuid);
        const target = index + direction;
        if (target < 0 || target >= order.length) {
            return;
        }
        [order[index], order[target]] = [order[target], order[index]];
        onReorder?.(order);
        onReorderEnd?.();
        onProductFocus?.(target);
        setAnnouncement(
            t('Product moved to position {{ position }} of {{ count }}', { position: target + 1, count: order.length }),
        );
    };
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
                    <Reorder.Group
                        as="div"
                        axis="x"
                        values={comparedProducts.map((product) => product.uuid)}
                        onReorder={(order) => onReorder?.(order)}
                        className="grid grid-flow-col grid-cols-2 grid-rows-[auto_auto_auto_auto_auto_auto_auto_auto] md:grid-cols-[12rem_repeat(var(--comparison-products),minmax(0,1fr))]"
                        id="js-table-compare-head"
                        style={{ '--comparison-products': comparedProducts.length } as CSSProperties}
                    >
                        <div className="row-span-8 hidden w-48 bg-table-bg-default md:sticky md:left-0 md:z-above md:block" />
                        {comparedProducts.map((product, index) => (
                            <ProductComparisonHeadItem
                                key={product.uuid}
                                canReorder={canReorder}
                                onReorderEnd={onReorderEnd}
                                onMove={(direction) => moveProduct(product.uuid, direction)}
                                columnIndex={index}
                                listIndex={allProducts.findIndex((item) => item.uuid === product.uuid)}
                                product={product}
                                stickyTriggerId={index === 0 ? PRODUCT_COMPARISON_STICKY_TRIGGER_ID : undefined}
                                toggleProductInComparison={() => onRemove(product)}
                            />
                        ))}
                    </Reorder.Group>
                    <span className="sr-only" role="status">
                        {announcement}
                    </span>
                </td>
            </tr>
        </thead>
    );
};
