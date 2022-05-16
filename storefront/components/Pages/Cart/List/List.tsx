import Item from './Item';
import { ListStyled } from './List.style';
import Webline from 'components/Layout/Webline';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type ListProps = {
    items?: CartItemType[];
};

const List: FC<ListProps> = (props) => {
    if (props.items === undefined) {
        return null;
    }

    return (
        <Webline>
            <ListStyled>
                {props.items.map((item) => (
                    <Item key={item.uuid} item={item} />
                ))}
            </ListStyled>
        </Webline>
    );
};

export default List;
