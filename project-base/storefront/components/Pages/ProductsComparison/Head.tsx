import { HeadItem } from './HeadItem';
import { ButtonRemoveAll } from 'components/Pages/ProductsComparison/ButtonRemoveAll';
import { ComparedProductFragmentApi } from 'graphql/generated';

type HeadProps = {
    productsCompare: ComparedProductFragmentApi[];
};

export const Head: FC<HeadProps> = (props) => (
    <thead>
        <tr id="js-table-compare-head">
            <td className="sticky left-0 z-above min-w-[115px] max-w-[205px] bg-white pr-3 align-top sm:w-52 sm:min-w-[211px] md:min-w-[256px] lg:w-72">
                <ButtonRemoveAll />
            </td>
            {props.productsCompare.map((product, index) => (
                <HeadItem
                    product={product}
                    key={`head-${product.uuid}`}
                    productsCompareCount={props.productsCompare!.length}
                    listIndex={index}
                />
            ))}
        </tr>
    </thead>
);
