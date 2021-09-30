import {
    ListItemInfo,
    ListItemInfoWrapper,
    ListItemPictureWrapper,
    ListItemPrice,
    ListItemStyled,
} from './OrderSummary.style';
import { CartItemType } from 'connectors/cart/types';
import { FC } from 'react';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image/Image';

type SingleProductProps = {
    item: CartItemType;
};

const SingleProduct: FC<SingleProductProps> = (props) => {
    return (
        <ListItemStyled>
            <ListItemPictureWrapper>
                <Image image={props.item.product.image} alt={props.item.product.fullName} />
            </ListItemPictureWrapper>
            <ListItemInfoWrapper>
                <ListItemInfo>
                    <strong>
                        {props.item.quantity} {props.item.product.unit.name} &nbsp;
                    </strong>
                    {props.item.product.fullName}
                </ListItemInfo>
                <ListItemPrice>
                    {formatPrice(props.item.product.price.priceWithVat, props.item.product.price.currencyCode)}
                </ListItemPrice>
            </ListItemInfoWrapper>
        </ListItemStyled>
    );
};

export default SingleProduct;
