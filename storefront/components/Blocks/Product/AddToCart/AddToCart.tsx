import { FC, useRef } from 'react';
import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import Button from 'components/Forms/Button';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCart } from 'connectors/cart/Cart';
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
    const testIdentifier = 'blocks-product-addtocart';

    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const cartUuid = useShopsysSelector((state) => state.user.cartUuid);
    const [changeCartItemQuantityResult, changeCartItemQuantity] = useAddToCart();
    const [popupData, setPopupData] = useHandleAddToCartMessage(changeCartItemQuantityResult, props.productUuid);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        changeCartItemQuantity({
            input: {
                cartUuid,
                isAbsoluteQuantity: false,
                productUuid: props.productUuid,
                quantity: spinboxRef.current.valueAsNumber,
            },
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
            <Button
                type="button"
                size="small"
                name="add-to-cart"
                onClick={onAddToCartHandler}
                data-testid={testIdentifier}
            >
                {t('Add to cart')}
            </Button>
            {popupData !== null && (
                <AddToCartPopup isVisible={true} onCloseCallback={() => setPopupData(null)} product={popupData} />
            )}
        </>
    );
};

export default AddToCart;
