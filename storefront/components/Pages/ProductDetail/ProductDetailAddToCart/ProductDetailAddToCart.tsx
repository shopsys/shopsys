import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { FC, useRef } from 'react';
import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import { formatPrice } from 'utils/formatting';
import { ProductDetailType } from 'types/product';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCart } from 'connectors/cart/Cart';
import { useHandleAddToCartMessage } from 'hooks/cart/useHandleAddToCartMessage';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cart.cartInput);
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useAddToCart();
    const [popupData, setPopupData] = useHandleAddToCartMessage(changeCartItemQuantityResult, props.product.uuid);

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
        <>
            <AddToCartWrapperStyled>
                <AddToCartPriceStyled>
                    {formatPrice(props.product.price.priceWithVat, props.product.price.currencyCode, t)}
                </AddToCartPriceStyled>
                <AddToCartFormStyled>
                    <AddToCartButtonsWrapperStyled>
                        <Spinbox min={1} step={1} defaultValue={1} max={props.product.stockQuantity} ref={spinboxRef} />
                        <AddToCartButtonWrapperStyled>
                            <AddToCartButtonStyled onClick={onAddToCartHandler} variant="primary">
                                {t('Add to cart')}
                            </AddToCartButtonStyled>
                        </AddToCartButtonWrapperStyled>
                    </AddToCartButtonsWrapperStyled>
                </AddToCartFormStyled>
            </AddToCartWrapperStyled>
            {popupData !== null && (
                <AddToCartPopup
                    isVisible={popupData !== null}
                    onCloseCallback={() => setPopupData(null)}
                    product={popupData}
                />
            )}
        </>
    );
};

export default ProductDetailAddToCart;
