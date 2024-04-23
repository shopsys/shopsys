import { CartIcon } from 'components/Basic/Icon/IconsSvg';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { ProductDetailFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';

type ProductDetailAddToCartProps = {
    product: ProductDetailFragmentApi;
};

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Product/AddToCartPopup').then((component) => component.AddToCartPopup),
);

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const [changeCartItemQuantity, fetching] = useAddToCart(
        GtmMessageOriginType.product_detail_page,
        GtmProductListNameType.product_detail,
    );
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onAddToCartHandler = async () => {
        if (!spinboxRef.current) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(product.uuid, spinboxRef.current.valueAsNumber);
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
        <div className="flex flex-col gap-4 rounded bg-blueLight p-3">
            <div className="text-2xl font-bold text-primary">{formatPrice(product.price.priceWithVat)}</div>
            {product.isSellingDenied ? (
                <p>{t('This item can no longer be purchased')}</p>
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
                                className="w-full"
                                isDisabled={fetching}
                                tid={TIDs.pages_productdetail_addtocart_button}
                                variant="primary"
                                onClick={onAddToCartHandler}
                            >
                                {fetching ? <Loader className="w-[18px]" /> : <CartIcon className="w-[18px]" />}

                                {t('Add to cart')}
                            </Button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
