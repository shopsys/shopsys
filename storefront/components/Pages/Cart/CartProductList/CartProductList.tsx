import CartProductListItem from './CartProductListItem';
import { CartType } from 'connectors/cart/types';
import { FC } from 'react';
import { StyledCartProductList } from './CartProductList.style';
import Webline from 'components/layout/Webline';

type CartProductListType = {
    cart?: CartType;
};

const CartProductList: FC<CartProductListType> = (props) => {
    if (props.cart === undefined) {
        return null;
    }

    return (
        <Webline>
            <StyledCartProductList>
                {props.cart.items.map((item) => (
                    <CartProductListItem key={item.uuid} item={item} cartUuid={props.cart!.uuid} />
                ))}
            </StyledCartProductList>
        </Webline>
    );
};

export default CartProductList;
