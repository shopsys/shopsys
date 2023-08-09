import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemFragmentApi } from 'graphql/generated';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { twMergeCustom } from 'helpers/twMerge';

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Product/AddToCartPopup').then((component) => component.AddToCartPopup),
);

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
    maxQuantity: number;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'blocks-product-addtocart';

export const AddToCart: FC<AddToCartProps> = ({
    productUuid,
    minQuantity,
    maxQuantity,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    className,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const [changeCartItemQuantity, fetching] = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const [popupData, setPopupData] = useState<CartItemFragmentApi | undefined>(undefined);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(productUuid, spinboxRef.current.valueAsNumber, listIndex);
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(addToCartResult?.addProductResult.cartItem);
    };

    return (
        <div className={twMergeCustom('flex items-stretch justify-between gap-2', className)}>
            <Spinbox
                size="small"
                step={1}
                min={minQuantity}
                max={maxQuantity}
                defaultValue={1}
                ref={spinboxRef}
                id={productUuid}
            />
            <Button
                isDisabled={fetching}
                className="py-2"
                size="small"
                name="add-to-cart"
                onClick={onAddToCartHandler}
                dataTestId={TEST_IDENTIFIER}
            >
                {fetching ? (
                    <Loader className="w-4 text-white" />
                ) : (
                    <Icon iconType="icon" icon="Cart" className="text-white" />
                )}
                <span>{t('Add to cart')}</span>
            </Button>

            {!!popupData && (
                <AddToCartPopup onCloseCallback={() => setPopupData(undefined)} addedCartItem={popupData} />
            )}
        </div>
    );
};
