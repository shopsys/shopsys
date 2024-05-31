import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCart } from 'utils/cart/useAddToCart';

export type ProductDetailAddToCartProps = {
    product: TypeProductDetailFragment;
};

const AddToCartPopup = dynamic(
    () => import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
    { ssr: false },
);

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { addToCart, isAddingToCart } = useAddToCart(
        GtmMessageOriginType.product_detail_page,
        GtmProductListNameType.product_detail,
    );
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onAddToCartHandler = async () => {
        if (!spinboxRef.current) {
            return;
        }

        const addToCartResult = await addToCart(product.uuid, spinboxRef.current.valueAsNumber);
        spinboxRef.current!.valueAsNumber = 1;

        if (addToCartResult) {
            updatePortalContent(
                <AddToCartPopup
                    key={addToCartResult.addProductResult.cartItem.uuid}
                    addedCartItem={addToCartResult.addProductResult.cartItem}
                />,
            );
        }
    };

    return (
        <>
            {product.isSellingDenied ? (
                <p className="text-red">{t('This item can no longer be purchased')}</p>
            ) : (
                <div className="text-sm vl:text-base">
                    <div className="flex items-center justify-between">
                        <Spinbox
                            defaultValue={1}
                            id={product.uuid}
                            max={product.stockQuantity}
                            min={1}
                            ref={spinboxRef}
                            step={1}
                        />
                        <div className="ml-2 flex-1">
                            <Button
                                className="whitespace-nowrap px-4 sm:px-8 w-fit h-12"
                                isDisabled={isAddingToCart}
                                tid={TIDs.pages_productdetail_addtocart_button}
                                onClick={onAddToCartHandler}
                            >
                                {isAddingToCart ? <Loader className="w-[18px]" /> : <CartIcon className="w-[18px]" />}
                                {t('Add to cart')}
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};
