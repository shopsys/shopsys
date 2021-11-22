import { FC } from 'react';
import { ListedProductType } from 'connectors/products/types';
import ProductItem from './ListedItem';
import { ProductsListStyled } from './ProductsList.style';

type ListedProductsProps = { products: ListedProductType[] };

const ProductsList: FC<ListedProductsProps> = (props) => {
    return (
        <ProductsListStyled>
            {props.products.map((listedProductItem, index) => (
                <ProductItem key={index} {...listedProductItem} />
            ))}
        </ProductsListStyled>
    );
};

export default ProductsList;
