import { FC } from 'react';
import { ListedProductItemType } from 'components/Blocks/Product/types';
import ProductItem from './ListedItem';
import { ProductsListStyled } from './ProductsList.style';

type ListedProductsProps = { node: ListedProductItemType }[];

const ProductsList: FC<{ products: ListedProductsProps }> = (props) => {
    return (
        <ProductsListStyled>
            {props.products.map((listedProductItem, index) => (
                <ProductItem key={index} {...listedProductItem.node} />
            ))}
        </ProductsListStyled>
    );
};

export default ProductsList;
