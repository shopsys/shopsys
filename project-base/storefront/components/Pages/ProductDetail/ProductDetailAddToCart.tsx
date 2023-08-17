import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { CartItemFragmentApi, ProductDetailFragmentApi } from 'graphql/generated';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';

type ProductDetailAddToCartProps = {
    product: ProductDetailFragmentApi;
};

const TEST_IDENTIFIER = 'pages-productdetail-addtocart';

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Product/AddToCartPopup').then((component) => component.AddToCartPopup),
);

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const [changeCartItemQuantity, fetching] = useAddToCart(
        GtmMessageOriginType.product_detail_page,
        GtmProductListNameType.product_detail,
    );
    const [popupData, setPopupData] = useState<CartItemFragmentApi | undefined>();

    const onAddToCartHandler = async () => {
        if (!spinboxRef.current) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(product.uuid, spinboxRef.current.valueAsNumber);
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(addToCartResult?.addProductResult.cartItem);
    };

    return (
        <>
            <div className="mb-4 block rounded bg-blueLight p-3 lg:mb-3" data-testid={TEST_IDENTIFIER}>
                <div className="mb-4 text-2xl font-bold text-primary" data-testid={TEST_IDENTIFIER + '-price'}>
                    {formatPrice(product.price.priceWithVat)}
                </div>
                {product.isSellingDenied ? (
                    <p>{t('This item can no longer be purchased')}</p>
                ) : (
                    <div className="text-sm vl:text-base">
                        <div className="flex items-center justify-between">
                            <Spinbox
                                id={product.uuid}
                                min={1}
                                step={1}
                                defaultValue={1}
                                max={product.stockQuantity}
                                ref={spinboxRef}
                            />
                            <div className="ml-2 flex-1">
                                <Button
                                    isDisabled={fetching}
                                    className="w-full"
                                    onClick={onAddToCartHandler}
                                    variant="primary"
                                    dataTestId={TEST_IDENTIFIER + '-button'}
                                >
                                    {fetching ? <Loader className="w-7" /> : <Icon iconType="icon" icon="Cart" />}

                                    {t('Add to cart')}
                                </Button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
            {!!popupData && (
                <AddToCartPopup onCloseCallback={() => setPopupData(undefined)} addedCartItem={popupData} />
            )}
        </>
    );
};
