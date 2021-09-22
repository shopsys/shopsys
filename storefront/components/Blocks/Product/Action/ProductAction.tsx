import { FC, useRef } from 'react';
import { mapCart, useChangeCartItemQuantity } from 'connectors/cart/Cart';
import { ProductActionStyled, ProductActionWrapperStyled } from './ProductAction.style';
import { useShopsysDispatch, useShopsysSelector } from 'redux/store';
import Button from '../../../Forms/Button';
import { SliderProductItemType } from '../types';
import Spinbox from '../../../Forms/Spinbox';
import { useHandleChangeCartItemQuantity } from 'hooks/cart/UseHandleChangeCartItemQuantity';
import { userActions } from 'redux/store/UserStore';
import { useRouter } from 'next/dist/client/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ProductAction: FC<SliderProductItemType> = (props) => {
    const router = useRouter();
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const t = useTypedTranslationFunction();
    const cartUuid = useShopsysSelector((state) => state.user.cart?.uuid);
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const dispatch = useShopsysDispatch();
    const [, changeCartItemQuantity] = useChangeCartItemQuantity();

    const onAddToCartHandler = async () => {
        if (spinboxRef.current === null) {
            return;
        }

        const { data, error } = await changeCartItemQuantity({
            cartUuid,
            isAbsoluteQuantity: false,
            productUuid: props.uuid,
            quantity: spinboxRef.current.valueAsNumber,
        });
        if (data !== undefined) {
            useHandleChangeCartItemQuantity(data, error, props.uuid, props.name, t);
            dispatch(userActions.setCart(mapCart(data.AddToCart, currencyCode)));
        }
        spinboxRef.current!.valueAsNumber = 1;
    };

    if (props.isMainVariant) {
        return (
            <ProductActionStyled isButtonFullWidth={true}>
                <Button type="button" onClick={() => router.push(props.detailSlug)} name="choose-variant">
                    {t('Choose variant')}
                </Button>
            </ProductActionStyled>
        );
    }

    return (
        <ProductActionWrapperStyled>
            <ProductActionStyled isButtonFullWidth={false}>
                <Spinbox size="small" step={1} min={1} max={props.stockQuantity} defaultValue={1} ref={spinboxRef} />
                <Button type="button" size="small" name="add-to-cart" onClick={onAddToCartHandler}>
                    {t('Add to cart')}
                </Button>
            </ProductActionStyled>
        </ProductActionWrapperStyled>
    );
};

export default ProductAction;
