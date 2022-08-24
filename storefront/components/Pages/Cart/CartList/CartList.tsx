import { ListStyled } from './CartList.style';
import { CartListItem } from './CartListItem/CartListItem';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type CartListProps = {
    items?: CartItemType[];
};

export const CartList: FC<CartListProps> = (props) => {
    if (props.items === undefined) {
        return null;
    }

    return (
        <Webline>
            <ListStyled>
                {props.items.map((item, index) => (
                    <CartListItem key={item.uuid} item={item} listIndex={index} />
                ))}
            </ListStyled>
        </Webline>
    );
};
