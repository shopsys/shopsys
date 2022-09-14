import { OrderSummaryListStyled, ProductsPreviewStyled } from './OrderSummary.style';
import { SingleProduct } from './SingleProduct';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type ProductsPreviewProps = {
    cartItems: CartItemType[];
};

const TEST_IDENTIFIER = 'blocks-ordersummary-productspreview';

export const ProductsPreview: FC<ProductsPreviewProps> = ({ cartItems }) => {
    return (
        <ProductsPreviewStyled data-testid={TEST_IDENTIFIER}>
            <OrderSummaryListStyled>
                {cartItems.map((item) => (
                    <SingleProduct key={item.uuid} item={item} />
                ))}
            </OrderSummaryListStyled>
        </ProductsPreviewStyled>
    );
};
