import { FC } from 'react';
import { ListedProductItemType } from '../types';
import ProductItem from './ListedProductItem';

type ListedProductsProps = { node: ListedProductItemType }[];

const ProductsList: FC<{ products: ListedProductsProps }> = (props) => {
    return (
        <>
            {props.products.map((listedProductItem, index) => (
                <ProductItem key={index} {...listedProductItem.node} />
            ))}
        </>
    );
};

export default ProductsList;
