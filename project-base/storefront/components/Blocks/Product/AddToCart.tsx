import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { twMergeCustom } from 'utils/twMerge';

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
    const { t } = useTranslation();
    const [changeCartItemQuantity, fetching] = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const [popupData, setPopupData] = useState<TypeCartItemFragment | undefined>(undefined);

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
                defaultValue={1}
                id={productUuid}
                max={maxQuantity}
                min={minQuantity}
                ref={spinboxRef}
                size="small"
                step={1}
            />
            <Button
                className="py-2"
                isDisabled={fetching}
                name="add-to-cart"
                size="small"
                tid={TIDs.blocks_product_addtocart}
                onClick={onAddToCartHandler}
            >
                {fetching ? <Loader className="w-4 text-white" /> : <CartIcon className="w-4 text-white" />}
                <span>{t('Add to cart')}</span>
            </Button>

            {!!popupData && (
                <AddToCartPopup addedCartItem={popupData} onCloseCallback={() => setPopupData(undefined)} />
            )}
        </div>
    );
};
