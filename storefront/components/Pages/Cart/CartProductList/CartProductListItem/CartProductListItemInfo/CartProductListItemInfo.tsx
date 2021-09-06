import {
    CartProductAvailabilityMessageStyled,
    CartProductAvailabilityStyled,
    CartProductCodeStyled,
    CartProductFlagsStyled,
    CartProductNameStyled,
    CartProductNameTitleStyled,
    CartProductNameTitleTextStyled,
} from './CartProductListItemInfo.style';
import { CartItemType } from 'connectors/cart/types';
import { FC } from 'react';
import Link from 'next/link';
import ProductFlags from 'components/blocks/product/Flags/ProductFlags';
import { useTranslation } from 'react-i18next';

type CartProductListItemProps = {
    item: CartItemType;
};

const CartProductListItem: FC<CartProductListItemProps> = (props) => {
    const { t } = useTranslation();

    return (
        <>
            <CartProductNameStyled>
                <Link href={props.item.product.slug} passHref>
                    <CartProductNameTitleStyled>
                        <CartProductNameTitleTextStyled>
                            {props.item.product.namePrefix +
                                '\u00A0' +
                                props.item.product.name +
                                '\u00A0' +
                                props.item.product.nameSuffix}
                        </CartProductNameTitleTextStyled>
                        <CartProductFlagsStyled>
                            <ProductFlags flags={props.item.product.flags} />
                        </CartProductFlagsStyled>
                    </CartProductNameTitleStyled>
                </Link>
                <CartProductCodeStyled>
                    {t('Code')}: {props.item.product.catalogNumber}
                </CartProductCodeStyled>
            </CartProductNameStyled>
            <CartProductAvailabilityStyled>
                {props.item.product.availability}
                <CartProductAvailabilityMessageStyled>
                    {props.item.product.availableStoresCount > 0 &&
                        t('(1)[or immediately in {{ count }} store];(2-inf)[or immediately in {{ count }} stores];', {
                            postProcess: 'interval',
                            count: props.item.product.availableStoresCount,
                        })}
                </CartProductAvailabilityMessageStyled>
            </CartProductAvailabilityStyled>
        </>
    );
};

export default CartProductListItem;
