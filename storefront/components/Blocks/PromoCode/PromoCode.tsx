import { Controller, useForm, useWatch } from 'react-hook-form';
import { FC, useEffect, useRef, useState } from 'react';
import {
    PromoCodeButtonIconStyled,
    PromoCodeButtonStyled,
    PromoCodeContentButtonStyled,
    PromoCodeContentInputStyled,
    PromoCodeContentStyled,
    PromoCodeContentWrapperStyled,
    PromoCodeStyled,
} from './PromoCode.style';
import { CSSTransition } from 'react-transition-group';
import { loadCart } from 'connectors/cart/Cart';
import PromoCodeInfo from './PromoCodeInfo';
import { showSuccessMessage } from 'components/Helpers/Toasts';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const PromoCode: FC = () => {
    const { cartUuid, payment, transport, promoCode } = useShopsysSelector((state) => state.cartInput);
    const [updatedPromoCode, updatePromoCode] = useState(promoCode);
    const [result] = loadCart(cartUuid, transport, payment, updatedPromoCode);
    const t = useTypedTranslationFunction();
    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const formProviderMethods = useForm({ defaultValues: { promoCode: promoCode === null ? '' : promoCode } });
    const promoCodeValue = useWatch({ name: 'promoCode', control: formProviderMethods.control });

    useEffect(() => {
        if (result.data === undefined || updatedPromoCode === promoCode || updatedPromoCode === null) {
            return;
        }
        if (result.data.cart !== null && result.error === undefined) {
            showSuccessMessage(t('Promo code was added to the order.'));
        }
    }, [result.data]);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    const onApplyPromoCodeHandler = (promoCode: string) => {
        updatePromoCode(promoCode);
    };

    const onRemovePromoCodeHandler = () => {
        formProviderMethods.setValue('promoCode', '');
        updatePromoCode(null);
    };

    return (
        <PromoCodeStyled contentElementHeight={contentElementHeight}>
            {promoCode !== null ? (
                <PromoCodeInfo promoCode={promoCode} onRemovePromoCodeCallback={onRemovePromoCodeHandler} />
            ) : (
                <>
                    <PromoCodeButtonStyled onClick={() => setIsContentVisible(!isContentVisible)}>
                        <PromoCodeButtonIconStyled icon="Plus" />
                        {t('I have a discount coupon')}
                    </PromoCodeButtonStyled>
                    <CSSTransition
                        in={isContentVisible}
                        timeout={300}
                        classNames="promoCode"
                        onEnter={calcHeight}
                        onExit={calcHeight}
                        unmountOnExit
                        nodeRef={cssTransitionRef}
                    >
                        <PromoCodeContentWrapperStyled ref={cssTransitionRef}>
                            <PromoCodeContentStyled ref={contentElement}>
                                <Controller
                                    name="promoCode"
                                    control={formProviderMethods.control}
                                    render={({ field }) => (
                                        <>
                                            <PromoCodeContentInputStyled
                                                type="text"
                                                id={field.name}
                                                label={t('Coupon')}
                                                fieldRef={field}
                                                style={{ width: '100%', marginBottom: '0' }}
                                            />
                                            <PromoCodeContentButtonStyled
                                                type="submit"
                                                isDisabled={
                                                    (typeof field.value === 'string' && field.value.length === 0) ||
                                                    result.fetching ||
                                                    promoCodeValue === updatedPromoCode
                                                }
                                                onClick={() => onApplyPromoCodeHandler(field.value as string)}
                                            >
                                                {t('Apply')}
                                            </PromoCodeContentButtonStyled>
                                        </>
                                    )}
                                />
                            </PromoCodeContentStyled>
                        </PromoCodeContentWrapperStyled>
                    </CSSTransition>
                </>
            )}
        </PromoCodeStyled>
    );
};

export default PromoCode;
