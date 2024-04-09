import { CategoryDetailProductsWrapperProps } from './CategoryDetailProductsWrapper';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { ProductListItemPlaceholder } from 'components/Blocks/Product/ProductsList/ProductListItemPlaceholder';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';

const productListTwClass = 'relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6';

type CategoryDetailProductsWrapperPlaceholderProps = Pick<CategoryDetailProductsWrapperProps, 'category' | 'products'>;

export const CategoryDetailProductsWrapperPlaceholder: FC<CategoryDetailProductsWrapperPlaceholderProps> = ({
    category,
    products,
}) => {
    if (!products?.length) {
        return <CategoryDetailContentMessage />;
    }

    return (
        <ul className={productListTwClass}>
            {products.map((product) => (
                <ProductListItemPlaceholder key={product.uuid} product={product} />
            ))}
            <Adverts
                isSingle
                className="col-span-full row-start-2 mx-auto justify-center pl-2"
                currentCategory={category}
                positionName="productListSecondRow"
            />
        </ul>
    );
};
