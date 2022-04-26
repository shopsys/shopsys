import { FC, useRef } from 'react';
import {
    ImageCellStyled,
    ImageWrapperStyled,
    InfoCellStyled,
    ItemPriceCellStyled,
    ItemPriceStyled,
    ItemStyled,
    RemoveButtonCellStyled,
    SpinboxCellStyled,
    TotalPriceCellStyled,
    TotalPriceStyled,
} from './Item.style';
import { CartItemType } from 'types/cart';
import { formatPrice } from 'utils/formatting';
import Image from 'components/Basic/Image';
import ItemInfo from './ItemInfo';
import NextLink from 'next/link';
import RemoveCartItemButton from 'components/Pages/Cart/RemoveCartItemButton';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCart } from 'connectors/cart/Cart';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ItemProps = {
    item: CartItemType;
};

const Item: FC<ItemProps> = (props) => {
    const testIdentifier = 'pages-cart-list-item-';
    const itemCatnum = props.item.product.catalogNumber;

    const timeoutRef = useRef<NodeJS.Timeout | null>(null);
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [, changeCartItemQuantity] = useAddToCart();

    const onChangeValueHandler = () => {
        if (timeoutRef.current === null) {
            timeoutRef.current = setUpdateTimeout();
        } else {
            clearTimeout(timeoutRef.current);
            timeoutRef.current = setUpdateTimeout();
        }
    };

    const setUpdateTimeout = () => {
        return setTimeout(() => {
            changeCartItemQuantity({
                input: {
                    productUuid: props.item.product.uuid,
                    cartUuid: cartUuid!,
                    quantity: spinboxRef.current!.valueAsNumber,
                    isAbsoluteQuantity: true,
                },
            });
        }, 500);
    };

    return (
        <ItemStyled data-testid={testIdentifier + itemCatnum}>
            <ImageCellStyled data-testid={testIdentifier + 'image'}>
                <NextLink href={props.item.product.slug} passHref>
                    <ImageWrapperStyled>
                        <Image
                            image={props.item.product.image}
                            type="thumbnailExtraSmall"
                            alt={props.item.product.fullName}
                        />
                    </ImageWrapperStyled>
                </NextLink>
            </ImageCellStyled>
            <InfoCellStyled>
                <ItemInfo item={props.item} />
            </InfoCellStyled>
            <SpinboxCellStyled data-testid={testIdentifier + 'spinbox'}>
                <Spinbox
                    min={1}
                    max={props.item.product.stockQuantity}
                    step={1}
                    defaultValue={props.item.quantity}
                    ref={spinboxRef}
                    onChangeValueCallback={onChangeValueHandler}
                />
            </SpinboxCellStyled>
            <ItemPriceCellStyled data-testid={testIdentifier + 'itemprice'}>
                <ItemPriceStyled>
                    {formatPrice(props.item.product.price.priceWithVat, props.item.product.price.currencyCode, t) +
                        '\u00A0/\u00A0' +
                        t('pc')}
                </ItemPriceStyled>
            </ItemPriceCellStyled>
            <TotalPriceCellStyled data-testid={testIdentifier + 'totalprice'}>
                <TotalPriceStyled>
                    {formatPrice(
                        props.item.product.price.priceWithVat * props.item.quantity,
                        props.item.product.price.currencyCode,
                        t,
                    )}
                </TotalPriceStyled>
            </TotalPriceCellStyled>
            <RemoveButtonCellStyled>
                <RemoveCartItemButton cartItemUuid={props.item.uuid} />
            </RemoveButtonCellStyled>
        </ItemStyled>
    );
};

export default Item;
