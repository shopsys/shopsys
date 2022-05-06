import {
    AddToCartButtonStyled,
    AddToCartButtonsWrapperStyled,
    AddToCartButtonWrapperStyled,
    AddToCartFormStyled,
    AddToCartPriceStyled,
    AddToCartWrapperStyled,
} from './ProductDetailAddToCart.style';
import { FC, useRef, useState } from 'react';
import AddToCartPopup from 'components/Blocks/Product/AddToCartPopup';
import { AddToCartPopupDataType } from 'types/cart';
import { formatPrice } from 'utils/formatting';
import { mapAddToCartPopupData } from 'connectors/cart/Cart';
import { ProductDetailType } from 'types/product';
import Spinbox from 'components/Forms/Spinbox';
import { useAddToCart } from 'hooks/cart/UseAddToCart';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type ProductDetailAddToCartProps = {
    product: ProductDetailType;
};

const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = (props) => {
    const testIdentifier = 'pages-productdetail-addtocart';

    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const changeCartItemQuantity = useAddToCart();
    const [popupData, setPopupData] = useState<AddToCartPopupDataType | null>(null);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const addToCartResult = await changeCartItemQuantity(props.product.uuid, spinboxRef.current.valueAsNumber);
        spinboxRef.current!.valueAsNumber = 1;
        setPopupData(mapAddToCartPopupData(addToCartResult, currencyCode));
    };

    return (
        <>
            <AddToCartWrapperStyled data-testid={testIdentifier}>
                <AddToCartPriceStyled data-testid={testIdentifier + '-price'}>
                    {formatPrice(props.product.price.priceWithVat, props.product.price.currencyCode, t)}
                </AddToCartPriceStyled>
                <AddToCartFormStyled>
                    <AddToCartButtonsWrapperStyled>
                        <Spinbox min={1} step={1} defaultValue={1} max={props.product.stockQuantity} ref={spinboxRef} />
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
            </AddToCartWrapperStyled>
            {popupData !== null && (
                <AddToCartPopup isVisible={true} onCloseCallback={() => setPopupData(null)} product={popupData} />
            )}
        </>
    );
};

export default ProductDetailAddToCart;
