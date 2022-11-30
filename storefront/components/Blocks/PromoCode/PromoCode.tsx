import {
    PromoCodeButtonIconStyled,
    PromoCodeButtonStyled,
    PromoCodeContentButtonStyled,
    PromoCodeContentInputStyled,
    PromoCodeContentStyled,
    PromoCodeContentWrapperStyled,
    PromoCodeStyled,
} from './PromoCode.style';
import { PromoCodeInfo } from './PromoCodeInfo/PromoCodeInfo';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { theme } from 'components/Theme/main';
import { useCurrentCart } from 'connectors/cart/Cart';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { useApplyPromoCodeToCart } from 'hooks/cart/useApplyPromoCodeToCart';
import { useRemovePromoCodeFromCart } from 'hooks/cart/useRemovePromoCodeFromCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { ChangeEventHandler, FC, MouseEventHandler, useCallback, useMemo, useRef, useState } from 'react';
import { CSSTransition } from 'react-transition-group';

type TransportAndPaymentErrorsType = {
    promoCode: {
        name: 'promoCode';
        label: string;
        errorMessage: string | undefined;
    };
};

const TEST_IDENTIFIER = 'blocks-promocode';

export const PromoCode: FC = () => {
    const { promoCode } = useCurrentCart();
    const t = useTypedTranslationFunction();
    const [isContentVisible, setIsContentVisible] = useState(false);
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const contentElement = useRef<HTMLDivElement>(null);
    const cssTransitionRef = useRef<HTMLDivElement>(null);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useState(false);
    const [promoCodeValue, setPromoCodeValue] = useState<string>(promoCode === null ? '' : promoCode);
    const [applyPromoCode, fetchingApplyPromoCode] = useApplyPromoCodeToCart();
    const [removePromoCode, fetchingRemovePromoCode] = useRemovePromoCodeFromCart();

    const promoCodeValidationMessages = useMemo(() => {
        const errors: Partial<TransportAndPaymentErrorsType> = {};

        if (promoCodeValue.length === 0) {
            errors.promoCode = {
                name: 'promoCode',
                label: t('Coupon'),
                errorMessage: t('This field is required'),
            };
        }

        return errors;
    }, [promoCodeValue, t]);

    const calcHeight = () => {
        if (contentElement.current) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    };

    const onApplyPromoCodeHandler: MouseEventHandler<HTMLButtonElement> = useCallback(async () => {
        if (hasValidationErrors(promoCodeValidationMessages)) {
            setErrorPopupVisibility(true);

            return;
        }

        applyPromoCode(promoCodeValue, {
            success: t('Promo code was added to the order.'),
            error: t('There was an error while adding a promo code to the order.'),
        });
    }, [applyPromoCode, promoCodeValidationMessages, promoCodeValue, t]);

    const onRemovePromoCodeHandler = useCallback(
        async (promoCode: string) => {
            removePromoCode(promoCode, {
                success: t('Promo code was removed from the order.'),
                error: t('There was an error while removing the promo code from the order.'),
            });
        },
        [removePromoCode, t],
    );

    const onChangePromoCodeValueHandler: ChangeEventHandler<HTMLInputElement> = useCallback(
        (event) => {
            setPromoCodeValue(event.currentTarget.value);
        },
        [setPromoCodeValue],
    );

    return (
        <>
            <PromoCodeStyled contentElementHeight={contentElementHeight} data-testid={TEST_IDENTIFIER}>
                {promoCode !== null ? (
                    <>
                        {fetchingRemovePromoCode && <LoaderWithOverlay iconSize={20} />}
                        <PromoCodeInfo promoCode={promoCode} onRemovePromoCodeCallback={onRemovePromoCodeHandler} />
                    </>
                ) : (
                    <>
                        <PromoCodeButtonStyled
                            onClick={() => setIsContentVisible(!isContentVisible)}
                            data-testid={TEST_IDENTIFIER + '-add-button'}
                        >
                            <PromoCodeButtonIconStyled alt="" iconType="icon" icon="Plus" />
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
                                    <PromoCodeContentInputStyled
                                        id={TEST_IDENTIFIER + '-input'}
                                        type="text"
                                        label={t('Coupon')}
                                        value={promoCodeValue}
                                        onChange={onChangePromoCodeValueHandler}
                                    />
                                    <PromoCodeContentButtonStyled
                                        type="submit"
                                        hasDisabledLook={hasValidationErrors(promoCodeValidationMessages)}
                                        data-testid={TEST_IDENTIFIER + '-apply-button'}
                                        onClick={onApplyPromoCodeHandler}
                                    >
                                        {fetchingApplyPromoCode && <Loader iconSize={16} color={theme.color.white} />}
                                        {t('Apply')}
                                    </PromoCodeContentButtonStyled>
                                </PromoCodeContentStyled>
                            </PromoCodeContentWrapperStyled>
                        </CSSTransition>
                    </>
                )}
            </PromoCodeStyled>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={promoCodeValidationMessages}
                origin="cart"
            />
        </>
    );
};
