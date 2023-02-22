import { AddToCartButtonStyled } from './ProductDetailAddToCart.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { AddToCartPopup } from 'components/Blocks/Product/AddToCartPopup/AddToCartPopup';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { mapAddToCartPopupData } from 'connectors/cart/Cart';
import { useAddToCart } from 'hooks/cart/useAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { ProductDetailType } from 'types/product';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

const TEST_IDENTIFIER = 'pages-productdetail-addtocart';

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const [changeCartItemQuantity, fetching] = useAddToCart('product');
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(product.uuid, spinboxRef.current.valueAsNumber, 'detail');
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(mapAddToCartPopupData(addToCartResult, currencyCode));
    };

    return (
        <>
            <div className="mb-4 block rounded-xl bg-blueLight p-3 lg:mb-3" data-testid={TEST_IDENTIFIER}>
                <div className="mb-4 text-2xl font-bold text-primary" data-testid={TEST_IDENTIFIER + '-price'}>
                    {formatPrice(product.price.priceWithVat)}
                </div>
                {product.isSellingDenied ? (
                    <p>{t('This item can no longer be purchased')}</p>
                ) : (
                    <div className="text-sm vl:text-base">
                        <div className="flex items-center justify-between">
                            <Spinbox min={1} step={1} defaultValue={1} max={product.stockQuantity} ref={spinboxRef} />
                            <div className="ml-2 flex-1">
                                <AddToCartButtonStyled
                                    onClick={onAddToCartHandler}
                                    variant="primary"
                                    data-testid={TEST_IDENTIFIER + '-button'}
                                >
                                    {fetching ? <Loader iconSize={26} /> : <Icon iconType="icon" icon="Cart" />}

                                    {t('Add to cart')}
                                </AddToCartButtonStyled>
                            </div>
                        </div>
                    </div>
                )}
            </div>
            {popupData !== null && (
                <AddToCartPopup isVisible onCloseCallback={() => setPopupData(null)} product={popupData} />
            )}
        </>
    );
};
