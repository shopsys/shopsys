import { AddToCartPopup } from 'components/Blocks/Product/AddToCartPopup/AddToCartPopup';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { mapAddToCartPopupData } from 'connectors/cart/Cart';
import { useAddToCart } from 'hooks/cart/UseAddToCart';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { GtmListNameType } from 'types/gtm';

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
    maxQuantity: number;
    gtmListName: GtmListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'blocks-product-addtocart';

export const AddToCart: FC<AddToCartProps> = ({ productUuid, minQuantity, maxQuantity, gtmListName, listIndex }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const changeCartItemQuantity = useAddToCart(gtmListName);
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(
            productUuid,
            listIndex,
            spinboxRef.current.valueAsNumber,
            gtmListName,
        );
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(mapAddToCartPopupData(addToCartResult, currencyCode));
    };

    return (
        <>
            <Spinbox size="small" step={1} min={minQuantity} max={maxQuantity} defaultValue={1} ref={spinboxRef} />
            <Button
                type="button"
                size="small"
                name="add-to-cart"
                onClick={onAddToCartHandler}
                data-testid={TEST_IDENTIFIER}
            >
                {t('Add to cart')}
            </Button>
            {popupData !== null && (
                <AddToCartPopup isVisible onCloseCallback={() => setPopupData(null)} product={popupData} />
            )}
        </>
    );
};
