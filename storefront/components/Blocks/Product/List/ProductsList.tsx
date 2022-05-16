import ProductItem from './ListedItem';
import { ProductsListStyled } from './ProductsList.style';
import { FC } from 'react';
import { ListedProductType } from 'types/product';

type ProductsListProps = {
    products: ListedProductType[];
};

const ProductsList: FC<ProductsListProps> = (props) => {
    const testIdentifier = 'blocks-product-list';

    return (
        <ProductsListStyled data-testid={testIdentifier}>
            {props.products.map((listedProductItem, index) => (
                <ProductItem key={index} {...listedProductItem} />
            ))}
        </ProductsListStyled>
    );
};

export default ProductsList;
