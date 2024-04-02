import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';
import { ProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { useComparison } from 'utils/productLists/comparison/useComparison';

type ProductComparisonHeadProps = {
    comparedProducts: ProductInProductListFragment[];
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({ comparedProducts }) => {
    const { toggleProductInComparison } = useComparison();

    return (
        <thead>
            <tr id="js-table-compare-head">
                <td className="sticky left-0 z-above min-w-[115px] max-w-[205px] bg-white pr-3 align-top sm:w-52 sm:min-w-[211px] md:min-w-[256px] lg:w-72">
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
