import { FC } from 'react';
import { ProductSliderStyled } from './ProductsSlider.style';
import SliderProductItem from './SliderProductItem';
import { SliderProductItemType } from './types';

type ProductsSliderProps = { products: SliderProductItemType[] };

const ProductsSlider: FC<ProductsSliderProps> = (props) => {
    return (
        <ProductSliderStyled>
            {props.products.map((productItemData, index) => (
                <SliderProductItem key={index} {...productItemData} />
            ))}
        </ProductSliderStyled>
    );
};

export default ProductsSlider;
