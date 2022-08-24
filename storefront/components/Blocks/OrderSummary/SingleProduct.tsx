import {
    ListItemInfoStyled,
    ListItemInfoWrapperStyled,
    ListItemPictureWrapperStyled,
    ListItemPriceStyled,
    ListItemStyled,
} from './OrderSummary.style';
import { Image } from 'components/Basic/Image/Image';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { FC } from 'react';
import { CartItemType } from 'types/cart';

type SingleProductProps = {
    item: CartItemType;
};

export const SingleProduct: FC<SingleProductProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-singleproduct-';

    const formatPrice = useFormatPrice();

    return (
        <ListItemStyled>
            <ListItemPictureWrapperStyled data-testid={testIdentifier + 'image'}>
                <Image image={props.item.product.image} type="thumbnailExtraSmall" alt={props.item.product.fullName} />
            </ListItemPictureWrapperStyled>
            <ListItemInfoWrapperStyled>
                <ListItemInfoStyled>
                    <strong data-testid={testIdentifier + 'count'}>
                        {props.item.quantity} {props.item.product.unit.name} &nbsp;
                    </strong>
                    <span data-testid={testIdentifier + 'name'}>{props.item.product.fullName}</span>
                </ListItemInfoStyled>
                <ListItemPriceStyled data-testid={testIdentifier + 'price'}>
                    {formatPrice(props.item.product.price.priceWithVat * props.item.quantity)}{' '}
                </ListItemPriceStyled>
            </ListItemInfoWrapperStyled>
        </ListItemStyled>
    );
};
