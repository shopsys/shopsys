import { FC, useRef } from 'react';
import { ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import Button from 'components/Forms/Button';
import Portal from 'components/Basic/Portal';
import { SliderProductItemType } from 'components/Blocks/Product/types';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCartMutationApi } from 'graphql/generated';
import { useHandleAddToCart } from 'hooks/cart/UseHandleAddToCart';
import { useHandleAddToCartMessage } from 'hooks/cart/useHandleAddToCartMessage';
import { useRouter } from 'next/dist/client/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useAddToCartMutationApi();

    useHandleAddToCart(
        changeCartItemQuantityResult,
        transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        promoCode,
    );

    const [popupData, setPopupData] = useHandleAddToCartMessage(changeCartItemQuantityResult, props.uuid);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        changeCartItemQuantity({
            cartUuid,
            isAbsoluteQuantity: false,
            productUuid: props.uuid,
            quantity: spinboxRef.current.valueAsNumber,
            transport,
            payment,
            promoCode,
        });
        spinboxRef.current!.valueAsNumber = 1;
    };

    if (props.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <Button type="button" onClick={() => router.push(props.slug)} name="choose-variant">
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    return (
        <>
            <ProductActionWrapperStyled>
                <ProductActionStyled isButtonFullWidth={false}>
                    <Spinbox
                        size="small"
                        step={1}
                        min={1}
                        max={props.stockQuantity}
                        defaultValue={1}
                        ref={spinboxRef}
                    />
                    <Button type="button" size="small" name="add-to-cart" onClick={onAddToCartHandler}>
                        {t('Add to cart')}
                    </Button>
                </ProductActionStyled>
            </ProductActionWrapperStyled>
            {popupData !== null && (
                <Portal>
                    <AddToCartPopup
                        isVisible={popupData !== null}
                        onCloseCallback={() => setPopupData(null)}
                        product={popupData}
                    />
                </Portal>
            )}
        </>
    );
};

export default ProductAction;
