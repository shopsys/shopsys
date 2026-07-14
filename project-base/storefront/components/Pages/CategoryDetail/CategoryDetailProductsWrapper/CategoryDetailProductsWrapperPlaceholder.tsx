import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { ProductListItemPlaceholder } from 'components/Blocks/Product/ProductsList/ProductListItemPlaceholder';
import {
    productListTwClass,
    productListViewModeListTwClass,
} from 'components/Blocks/Product/ProductsList/ProductsList';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { useCookiesStore } from 'store/useCookiesStore';
import { CategoryDetailProductsWrapperProps } from './CategoryDetailProductsWrapper';

type CategoryDetailProductsWrapperPlaceholderProps = Pick<CategoryDetailProductsWrapperProps, 'category' | 'products'>;

export const CategoryDetailProductsWrapperPlaceholder: FC<CategoryDetailProductsWrapperPlaceholderProps> = ({
    category,
    products,
}) => {
    const productListViewMode = useCookiesStore((store) => store.productListViewMode);
    const productListPlaceholderTwClass =
        productListViewMode === 'grid' ? productListTwClass : productListViewModeListTwClass;

    if (!products?.length) {
        return <CategoryDetailContentMessage />;
    }

    return (
        <ul className={productListPlaceholderTwClass}>
            {products.map((product) => (
                <ProductListItemPlaceholder
                    key={product.uuid}
                    product={product}
                    productListViewMode={productListViewMode}
                />
            ))}
            <li className="col-span-full row-start-2 mx-auto justify-center">
                <Adverts isSingle currentCategory={category} positionName="productListSecondRow" />
            </li>
        </ul>
    );
};
