import { ProductComparisonButtonRemoveAll } from './ProductComparisonButtonRemoveAll';
import { ProductComparisonHeadItem } from './ProductComparisonHeadItem';
import { ComparedProductFragmentApi } from 'graphql/generated';
import { useComparison } from 'hooks/comparison/useComparison';

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
                        key={`head-${product.uuid}`}
                        listIndex={index}
                        product={product}
                        productsCompareCount={productsCompare!.length}
                        toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                    />
                ))}
            </tr>
        </thead>
    );
};
