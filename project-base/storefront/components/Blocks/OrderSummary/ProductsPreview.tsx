import { SingleProduct } from './SingleProduct';
import { CartItemFragmentApi } from 'graphql/generated';

type ProductsPreviewProps = {
    cartItems: CartItemFragmentApi[];
};

const TEST_IDENTIFIER = 'blocks-ordersummary-productspreview';

export const ProductsPreview: FC<ProductsPreviewProps> = ({ cartItems }) => {
    return (
        <div className="mb-5" data-testid={TEST_IDENTIFIER}>
            <ul className="m-0 list-none p-0">
                {cartItems.map((item) => (
                    <SingleProduct key={item.uuid} item={item} />
                ))}
            </ul>
        </div>
    );
};
