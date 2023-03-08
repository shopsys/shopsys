import { PromoCodeStyled } from './PromoCode.style';
import { PromoCodeInfo } from './PromoCodeInfo/PromoCodeInfo';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { useCurrentCart } from 'connectors/cart/Cart';
import { hasValidationErrors } from 'helpers/errors/hasValidationErrors';
import { useApplyPromoCodeToCart } from 'hooks/cart/useApplyPromoCodeToCart';
import { useRemovePromoCodeFromCart } from 'hooks/cart/useRemovePromoCodeFromCart';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { ChangeEventHandler, MouseEventHandler, useCallback, useMemo, useRef, useState } from 'react';
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
                        <div
                            className="mb-3 inline-flex cursor-pointer items-center rounded bg-orangeLight py-3 px-4 text-sm font-bold uppercase text-grey transition hover:bg-orangeLight"
                            onClick={() => setIsContentVisible(!isContentVisible)}
                            data-testid={TEST_IDENTIFIER + '-add-button'}
                        >
                            <Icon iconType="icon" icon="Plus" width={12} height={12} className="mr-3" />
                            {t('I have a discount coupon')}
                        </div>
                        <CSSTransition
                            in={isContentVisible}
                            timeout={300}
                            classNames="promoCode"
                            onEnter={calcHeight}
                            onExit={calcHeight}
                            unmountOnExit
                            nodeRef={cssTransitionRef}
                        >
                            <div className="overflow-hidden" ref={cssTransitionRef}>
                                <div className="flex" ref={contentElement}>
                                    <TextInput
                                        className="!mb-0 !w-full max-w-sm !rounded-r-none !border-r-0"
                                        id={TEST_IDENTIFIER + '-input'}
                                        type="text"
                                        label={t('Coupon')}
                                        value={promoCodeValue}
                                        onChange={onChangePromoCodeValueHandler}
                                    />
                                    <Button
                                        className="!rounded-r-xl !rounded-l-none !px-3"
                                        type="submit"
                                        hasDisabledLook={hasValidationErrors(promoCodeValidationMessages)}
                                        data-testid={TEST_IDENTIFIER + '-apply-button'}
                                        onClick={onApplyPromoCodeHandler}
                                    >
                                        {fetchingApplyPromoCode && <Loader iconSize={16} className="text-white" />}
                                        {t('Apply')}
                                    </Button>
                                </div>
                            </div>
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
