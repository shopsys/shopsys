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

const AddToCartPopup = dynamic(
    () => import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
    { ssr: false },
);

const InquiryPopup = dynamic(
    () => import('components/Blocks/Popup/InquiryPopup').then((component) => component.InquiryPopup),
    {
        ssr: false,
    },
);

export type ProductDetailAddToCartProps = {
    product: TypeProductDetailFragment;
};

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

    if (product.isSellingDenied) {
        return <p className="text-textError">{t('This item can no longer be purchased')}</p>;
    }

    if (product.isInquiryType) {
        const openInquiryPopup = (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
            e.stopPropagation();
            updatePortalContent(<InquiryPopup productUuid={product.uuid} />);
        };

        return (
            <Button className="w-fit" size="large" onClick={openInquiryPopup}>
                {t('Inquire')}
            </Button>
        );
    }

    return (
        <div className="flex items-center gap-2">
            <Spinbox defaultValue={1} id={product.uuid} max={product.stockQuantity} min={1} ref={spinboxRef} step={1} />

            <div className="relative">
                {isAddingToCart && (
                    <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded bg-backgroundMore py-2 opacity-50" />
                )}

                <Button
                    className="whitespace-nowrap"
                    isDisabled={isAddingToCart}
                    size="large"
                    tid={TIDs.pages_productdetail_addtocart_button}
                    onClick={onAddToCartHandler}
                >
                    <CartIcon className="w-[18px]" />
                    {t('Add to cart')}
                </Button>
            </div>
        </div>
    );
};
