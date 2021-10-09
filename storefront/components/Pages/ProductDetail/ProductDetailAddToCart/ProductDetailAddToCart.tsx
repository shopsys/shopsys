import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { FC, useEffect, useRef } from 'react';
import { formatPrice } from 'utils/formatting';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { showChangeCartItemQuantityMessages } from 'utils/Cart/ShowChangeCartItemQuantityMessages';
import Spinbox from 'components/Forms/Spinbox';
import { useChangeCartItemQuantity } from 'connectors/cart/Cart';
import { useHandleAddToCart } from 'hooks/cart/UseHandleAddToCart';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid } = useShopsysSelector((state) => state.cookie);
    const { transport, payment, promoCode } = useShopsysSelector((state) => state.cookie);
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useChangeCartItemQuantity();
    useHandleAddToCart(
        changeCartItemQuantityResult,
        transport?.personalPickupStoreUuid === undefined ? null : transport.personalPickupStoreUuid,
        promoCode,
    );
    useEffect(() => {
        showChangeCartItemQuantityMessages(changeCartItemQuantityResult, props.product.uuid, props.product.name, t);
    }, [changeCartItemQuantityResult]);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        changeCartItemQuantity({
            cartUuid,
            isAbsoluteQuantity: false,
            productUuid: props.product.uuid,
            quantity: spinboxRef.current.valueAsNumber,
            transport,
            payment,
            promoCode,
        });
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
