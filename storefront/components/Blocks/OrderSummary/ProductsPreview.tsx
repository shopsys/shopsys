import { OrderSummaryListStyled, ProductsPreviewStyled } from './OrderSummary.style';
import { CartItemType } from 'types/cart';
import { FC } from 'react';
import SingleProduct from './SingleProduct';

type ProductsPreviewProps = {
    cartItems: CartItemType[];
};

const ProductsPreview: FC<ProductsPreviewProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-productspreview';

    return (
        <ProductsPreviewStyled data-testid={testIdentifier}>
            <OrderSummaryListStyled>
                {props.cartItems.map((item) => (
                    <SingleProduct key={item.uuid} item={item} />
                ))}
            </OrderSummaryListStyled>
        </ProductsPreviewStyled>
    );
};

export default ProductsPreview;
