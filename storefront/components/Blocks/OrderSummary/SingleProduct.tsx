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

const TEST_IDENTIFIER = 'blocks-ordersummary-singleproduct-';

export const SingleProduct: FC<SingleProductProps> = ({ item }) => {
    const formatPrice = useFormatPrice();

    return (
        <ListItemStyled>
            <ListItemPictureWrapperStyled data-testid={TEST_IDENTIFIER + 'image'}>
                <Image image={item.product.image} type="thumbnailExtraSmall" alt={item.product.fullName} />
            </ListItemPictureWrapperStyled>
            <ListItemInfoWrapperStyled>
                <ListItemInfoStyled>
                    <strong data-testid={TEST_IDENTIFIER + 'count'}>
                        {item.quantity} {item.product.unit.name} &nbsp;
                    </strong>
                    <span data-testid={TEST_IDENTIFIER + 'name'}>{item.product.fullName}</span>
                </ListItemInfoStyled>
                <ListItemPriceStyled data-testid={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(item.product.price.priceWithVat * item.quantity)}{' '}
                </ListItemPriceStyled>
            </ListItemInfoWrapperStyled>
        </ListItemStyled>
    );
};
