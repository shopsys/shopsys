import {
    ListItemInfo,
    ListItemInfoWrapper,
    ListItemPictureWrapper,
    ListItemPrice,
    ListItemStyled,
} from './OrderSummary.style';
import Image from 'components/Basic/Image/Image';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC } from 'react';
import { CartItemType } from 'types/cart';
import { formatPrice } from 'utils/formatting';

type SingleProductProps = {
    item: CartItemType;
};

const SingleProduct: FC<SingleProductProps> = (props) => {
    const testIdentifier = 'blocks-ordersummary-singleproduct-';

    const t = useTypedTranslationFunction();

    return (
        <ListItemStyled>
            <ListItemPictureWrapper data-testid={testIdentifier + 'image'}>
                <Image image={props.item.product.image} type="thumbnailExtraSmall" alt={props.item.product.fullName} />
            </ListItemPictureWrapper>
            <ListItemInfoWrapper>
                <ListItemInfo>
                    <strong data-testid={testIdentifier + 'count'}>
                        {props.item.quantity} {props.item.product.unit.name} &nbsp;
                    </strong>
                    <span data-testid={testIdentifier + 'name'}>{props.item.product.fullName}</span>
                </ListItemInfo>
                <ListItemPrice data-testid={testIdentifier + 'price'}>
                    {formatPrice(
                        props.item.product.price.priceWithVat * props.item.quantity,
                        props.item.product.price.currencyCode,
                        t,
                    )}
                </ListItemPrice>
            </ListItemInfoWrapper>
        </ListItemStyled>
    );
};

export default SingleProduct;
