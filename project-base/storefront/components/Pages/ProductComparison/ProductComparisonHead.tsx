import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';

export const PRODUCT_COMPARISON_STICKY_TRIGGER_ID = 'js-product-comparison-sticky-trigger';
export const PRODUCT_COMPARISON_END_TRIGGER_ID = 'js-table-compare-wrap';

type ProductComparisonHeadProps = {
    comparedProducts: TypeProductInProductListFragment[];
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({ comparedProducts }) => {
    const { toggleProductInComparison } = useComparison();

    return (
        <thead>
            <tr id="js-table-compare-head">
                <td className="sticky left-0 z-above min-w-28.75 max-w-51.25 bg-table-bg-default pr-3 align-top sm:w-52 sm:min-w-52.75 md:min-w-[256px] lg:w-72" />

                {comparedProducts.map((product, index) => (
                    <ProductComparisonHeadItem
                        key={`head-${product.uuid}`}
                        listIndex={index}
                        product={product}
                        stickyTriggerId={index === 0 ? PRODUCT_COMPARISON_STICKY_TRIGGER_ID : undefined}
                        toggleProductInComparison={() =>
                            toggleProductInComparison(product, GtmProductListNameType.product_comparison_page, index)
                        }
                    />
                ))}
            </tr>
        </thead>
    );
};
