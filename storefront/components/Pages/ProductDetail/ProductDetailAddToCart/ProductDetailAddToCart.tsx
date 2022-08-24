import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartUnavailableTextStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { AddToCartPopup } from 'components/Blocks/Product/AddToCartPopup/AddToCartPopup';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { mapAddToCartPopupData } from 'connectors/cart/Cart';
import { useAddToCart } from 'hooks/cart/UseAddToCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AddToCartPopupDataType } from 'types/cart';
import { ProductDetailType } from 'types/product';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const testIdentifier = 'pages-productdetail-addtocart';

    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const formatPrice = useFormatPrice();
    const changeCartItemQuantity = useAddToCart('product');
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(
            props.product.uuid,
            1,
            spinboxRef.current.valueAsNumber,
            'detail',
        );
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(mapAddToCartPopupData(addToCartResult, currencyCode));
    };

    return (
        <>
            <AddToCartWrapperStyled data-testid={testIdentifier}>
                <AddToCartPriceStyled data-testid={testIdentifier + '-price'}>
                    {formatPrice(props.product.price.priceWithVat)}
                </AddToCartPriceStyled>
                {props.product.isSellingDenied ? (
                    <AddToCartUnavailableTextStyled>
                        {t('This item can no longer be purchased')}
                    </AddToCartUnavailableTextStyled>
                ) : (
                    <AddToCartFormStyled>
                        <AddToCartButtonsWrapperStyled>
                            <Spinbox
                                min={1}
                                step={1}
                                defaultValue={1}
                                max={props.product.stockQuantity}
                                ref={spinboxRef}
                            />
                            <AddToCartButtonWrapperStyled>
                                <AddToCartButtonStyled
                                    onClick={onAddToCartHandler}
                                    variant="primary"
                                    data-testid={testIdentifier + '-button'}
                                >
                                    {t('Add to cart')}
                                </AddToCartButtonStyled>
                            </AddToCartButtonWrapperStyled>
                        </AddToCartButtonsWrapperStyled>
                    </AddToCartFormStyled>
                )}
            </AddToCartWrapperStyled>
            {popupData !== null && (
                <AddToCartPopup isVisible={true} onCloseCallback={() => setPopupData(null)} product={popupData} />
            )}
        </>
    );
};
