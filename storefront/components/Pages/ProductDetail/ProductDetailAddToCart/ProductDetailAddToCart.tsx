import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { FC, useRef } from 'react';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import { formatPrice } from 'utils/formatting';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import Spinbox from 'components/Forms/Spinbox';
import { useChangeCartItemQuantity } from 'connectors/cart/Cart';
import { useHandleCartUpdate } from 'hooks/cart/UseHandleCartUpdate';
import { useHandleChangeCartItemQuantity } from 'hooks/cart/UseHandleChangeCartItemQuantity';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const cartUuid = useShopsysSelector((state) => state.user.cart?.uuid);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const [, changeCartItemQuantity] = useChangeCartItemQuantity();

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const { data, error } = await changeCartItemQuantity({
            cartUuid,
            isAbsoluteQuantity: false,
            productUuid: props.product.uuid,
            quantity: spinboxRef.current.valueAsNumber,
        });
        if (data !== undefined) {
            useHandleChangeCartItemQuantity(data, error, props.product.uuid, props.product.name, t);
            useHandleCartUpdate(data.AddToCart, currencyCode, dispatch);
        }
        spinboxRef.current!.valueAsNumber = 1;
    };

    return (
        <AddToCartWrapperStyled>
            <AddToCartPriceStyled>
                {formatPrice(props.product.price.priceWithVat, props.product.price.currencyCode, t)}
            </AddToCartPriceStyled>
            <AddToCartFormStyled>
                <AddToCartButtonsWrapperStyled>
                    <Spinbox min={1} step={1} defaultValue={1} max={props.product.stockQuantity} ref={spinboxRef} />
                    <AddToCartButtonWrapperStyled>
                        <AddToCartButtonStyled onClick={onAddToCartHandler}>{t('Add to cart')}</AddToCartButtonStyled>
                    </AddToCartButtonWrapperStyled>
                </AddToCartButtonsWrapperStyled>
            </AddToCartFormStyled>
        </AddToCartWrapperStyled>
    );
};

export default ProductDetailAddToCart;
