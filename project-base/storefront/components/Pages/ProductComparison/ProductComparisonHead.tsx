import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';
import { useComparison } from 'hooks/comparison/useComparison';
import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ComparedProductFragmentApi } from 'graphql/requests/products/fragments/ComparedProductFragment.generated';

type ProductComparisonHeadProps = {
    productsCompare: ComparedProductFragmentApi[];
};

export const ProductComparisonHead: FC<ProductComparisonHeadProps> = ({ productsCompare }) => {
    const { toggleProductInComparison } = useComparison();

    return (
        <thead>
            <tr id="js-table-compare-head">
                <td className="sticky left-0 z-above min-w-[115px] max-w-[205px] bg-white pr-3 align-top sm:w-52 sm:min-w-[211px] md:min-w-[256px] lg:w-72">
                    <ProductComparisonButtonRemoveAll />
                </td>
                {productsCompare.map((product, index) => (
                    <ProductComparisonHeadItem
                        product={product}
                        key={`head-${product.uuid}`}
                        productsCompareCount={productsCompare!.length}
                        listIndex={index}
                        toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                    />
                ))}
            </tr>
        </thead>
    );
};
