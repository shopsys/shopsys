import { CategoryDetailProductsWrapperProps } from './CategoryDetailProductsWrapper';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { ProductListItemPlaceholder } from 'components/Blocks/Product/ProductsList/ProductListItemPlaceholder';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { twJoin } from 'tailwind-merge';

export const productListTwClass = twJoin(
    'relative grid gap-2.5 sm:gap-x-5 sm:gap-y-6',
    'grid-cols-1',
    'xs:grid-cols-2',
    'lg:grid-cols-3',
    'xl:grid-cols-4',
);

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
