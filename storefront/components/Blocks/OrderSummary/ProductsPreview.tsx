import { OrderSummaryListStyled, ProductsPreviewStyled } from './OrderSummary.style';
import { CartItemType } from 'connectors/cart/types';
import { FC } from 'react';
import SingleProduct from './SingleProduct';

type ProductsPreviewProps = {
    cartItems: CartItemType[];
};

const ProductsPreview: FC<ProductsPreviewProps> = (props) => {
    return (
        <ProductsPreviewStyled>
            <OrderSummaryListStyled>
                {props.cartItems.map((item) => (
                    <SingleProduct key={item.uuid} item={item} />
                ))}
            </OrderSummaryListStyled>
        </ProductsPreviewStyled>
    );
};

export default ProductsPreview;
