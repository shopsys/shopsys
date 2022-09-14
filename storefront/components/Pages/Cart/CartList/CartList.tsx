import { ListStyled } from './CartList.style';
import { CartListItem } from './CartListItem/CartListItem';
import { Webline } from 'components/Layout/Webline/Webline';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type CartListProps = {
    items?: CartItemType[];
};

export const CartList: FC<CartListProps> = ({ items }) => {
    if (items === undefined) {
        return null;
    }

    return (
        <Webline>
            <ListStyled>
                {items.map((item, index) => (
                    <CartListItem key={item.uuid} item={item} listIndex={index} />
                ))}
            </ListStyled>
        </Webline>
    );
};
