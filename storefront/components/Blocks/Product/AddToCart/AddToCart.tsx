import { FC, useRef } from 'react';
import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import Button from 'components/Forms/Button';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCartMutationApi } from 'graphql/generated';
import { useHandleAddToCart } from 'hooks/cart/UseHandleAddToCart';
import { useHandleAddToCartMessage } from 'hooks/cart/useHandleAddToCartMessage';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type AddToCartProps = {
    productUuid: string;
    productName: string;
    minQuantity: number;
    maxQuantity: number;
};

const AddToCart: FC<AddToCartProps> = (props) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const { cartUuid, transport, payment, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useAddToCartMutationApi();
    const [popupData, setPopupData] = useHandleAddToCartMessage(changeCartItemQuantityResult, props.productUuid);

    useHandleAddToCart(
        changeCartItemQuantityResult,
        transport?.pickupPlaceIdentifier === undefined ? null : transport.pickupPlaceIdentifier,
        promoCode,
    );

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        changeCartItemQuantity({
            cartUuid,
            isAbsoluteQuantity: false,
            productUuid: props.productUuid,
            quantity: spinboxRef.current.valueAsNumber,
            transport,
            payment,
            promoCode,
        });
        spinboxRef.current!.valueAsNumber = 1;
    };

    return (
        <>
            <Spinbox
                size="small"
                step={1}
                min={props.minQuantity}
                max={props.maxQuantity}
                defaultValue={1}
                ref={spinboxRef}
            />
            <Button type="button" size="small" name="add-to-cart" onClick={onAddToCartHandler}>
                {t('Add to cart')}
            </Button>
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

export default AddToCart;
