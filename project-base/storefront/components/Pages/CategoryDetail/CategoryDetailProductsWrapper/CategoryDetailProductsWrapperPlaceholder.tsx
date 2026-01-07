import { CategoryDetailProductsWrapperProps } from './CategoryDetailProductsWrapper';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { ProductListItemPlaceholder } from 'components/Blocks/Product/ProductsList/ProductListItemPlaceholder';
import { productListTwClass } from 'components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';

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
            <li className="col-span-full row-start-2 mx-auto justify-center">
                <Adverts isSingle currentCategory={category} positionName="productListSecondRow" />
            </li>
        </ul>
    );
};
