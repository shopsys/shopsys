import { FC } from 'react';
import ProductItem from './ProductItem';
import { ProductItemType } from './types';

type ProductsSliderProps = { products: ProductItemType[] };

const ProductsSlider: FC<ProductsSliderProps> = (props) => {
    return (
        <ul>
            {props.products.map((productItemData, index) => (
                <li key={index}>
                    <ProductItem {...productItemData} />
                </li>
            ))}
        </ul>
    );
};

export default ProductsSlider;
