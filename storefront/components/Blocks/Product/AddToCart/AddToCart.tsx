import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import Button from 'components/Forms/Button';
import Spinbox from 'components/Forms/Spinbox';
import { mapAddToCartPopupData } from 'connectors/cart/Cart';
import { useAddToCart } from 'hooks/cart/UseAddToCart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { GtmListNameType } from 'types/gtm';

type AddToCartProps = {
    productUuid: string;
    productName: string;
    minQuantity: number;
    maxQuantity: number;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const AddToCart: FC<AddToCartProps> = (props) => {
    const testIdentifier = 'blocks-product-addtocart';

    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const changeCartItemQuantity = useAddToCart(props.gtmListName);
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(
            props.productUuid,
            props.listIndex,
            spinboxRef.current.valueAsNumber,
            props.gtmListName,
        );
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(mapAddToCartPopupData(addToCartResult, currencyCode));
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
