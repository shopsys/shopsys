import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { AddToCartPopup } from 'components/Blocks/Product/AddToCartPopup/AddToCartPopup';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemFragmentApi } from 'graphql/generated';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRef, useState } from 'react';
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
    const [changeCartItemQuantity, fetching] = useAddToCart(gtmListName);
    const [popupData, setPopupData] = useState<CartItemFragmentApi | undefined>(undefined);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(
            productUuid,
            spinboxRef.current.valueAsNumber,
            gtmListName,
            listIndex,
        );
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(addToCartResult?.addProductResult.cartItem);
    };

    return (
        <>
            <Spinbox size="small" step={1} min={minQuantity} max={maxQuantity} defaultValue={1} ref={spinboxRef} />
            <Button isSmall name="add-to-cart" onClick={onAddToCartHandler} dataTestId={TEST_IDENTIFIER}>
                {fetching ? (
                    <Loader iconSize={16} className="text-white" />
                ) : (
                    <Icon iconType="icon" icon="Cart" className="text-white" />
                )}
                <span>{t('Add to cart')}</span>
            </Button>
            {popupData !== undefined && (
                <AddToCartPopup isVisible onCloseCallback={() => setPopupData(undefined)} addedCartItem={popupData} />
            )}
        </>
    );
};
