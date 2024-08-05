import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useComparison } from 'utils/productLists/comparison/useComparison';

type ProductComparisonHeadProps = {
    comparedProducts: TypeProductInProductListFragment[];
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({ comparedProducts }) => {
    const { toggleProductInComparison } = useComparison();

    return (
        <thead>
            <tr id="js-table-compare-head">
                <td className="sticky left-0 z-[11] min-w-[115px] max-w-[205px] bg-tableBackground pr-3 align-top sm:w-52 sm:min-w-[211px] md:min-w-[256px] lg:w-72">
                    <ProductComparisonButtonRemoveAll />
                </td>
                {comparedProducts.map((product, index) => (
                    <ProductComparisonHeadItem
                        key={`head-${product.uuid}`}
                        listIndex={index}
                        product={product}
                        productsCompareCount={comparedProducts!.length}
                        toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                    />
                ))}
            </tr>
        </thead>
    );
};
