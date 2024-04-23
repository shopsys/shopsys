import { PromoCodeInfo } from './PromoCodeInfo';
import { PlusIcon } from 'components/Basic/Icon/PlusIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { TextInput } from 'components/Forms/TextInput/TextInput';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { ChangeEventHandler, MouseEventHandler, useCallback, useMemo, useRef, useState } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useRemovePromoCodeFromCart } from 'utils/cart/useRemovePromoCodeFromCart';
import { hasValidationErrors } from 'utils/errors/hasValidationErrors';

const ErrorPopup = dynamic(() =>
    import('components/Blocks/Popup/ErrorPopup').then((component) => component.ErrorPopup),
);

type TransportAndPaymentErrorsType = {
    promoCode: {
        name: 'promoCode';
        label: string;
        errorMessage: string | undefined;
    };
};

export const PromoCode: FC = () => {
    const { promoCode } = useCurrentCart();
    const { t } = useTranslation();
    const contentElement = useRef<HTMLDivElement>(null);
    const [promoCodeValue, setPromoCodeValue] = useState<string>(promoCode === null ? '' : promoCode);
    const [isContentVisible, setIsContentVisible] = useState(!!promoCodeValue);
    const [applyPromoCode, fetchingApplyPromoCode] = useApplyPromoCodeToCart();
    const [removePromoCode, fetchingRemovePromoCode] = useRemovePromoCodeFromCart();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const promoCodeValidationMessages = useMemo(() => {
        const errors: Partial<TransportAndPaymentErrorsType> = {};

        if (!promoCodeValue) {
            errors.promoCode = {
                name: 'promoCode',
                label: t('Coupon'),
                errorMessage: t('This field is required'),
            };
        }

        return errors;
    }, [promoCodeValue, t]);

    const onApplyPromoCodeHandler: MouseEventHandler<HTMLButtonElement> = useCallback(async () => {
        if (hasValidationErrors(promoCodeValidationMessages)) {
            updatePortalContent(
                <ErrorPopup fields={promoCodeValidationMessages} gtmMessageOrigin={GtmMessageOriginType.cart} />,
            );

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
        <div className="relative w-80">
            {promoCode !== null ? (
                <>
                    {fetchingRemovePromoCode && <LoaderWithOverlay className="w-5" />}
                    <PromoCodeInfo promoCode={promoCode} onRemovePromoCodeCallback={onRemovePromoCodeHandler} />
                </>
            ) : (
                <>
                    <Button
                        className="text-sm mb-3"
                        tid={TIDs.blocks_promocode_add_button}
                        onClick={() => setIsContentVisible(!isContentVisible)}
                    >
                        <PlusIcon className="w-3" />
                        {t('I have a discount coupon')}
                    </Button>
                    {isContentVisible && (
                        <div className="flex" ref={contentElement}>
                            <TextInput
                                className="!mb-0 !w-full max-w-sm !rounded-r-none !border-r-0"
                                id="blocks-promocode-input"
                                label={t('Coupon')}
                                type="text"
                                value={promoCodeValue}
                                onChange={onChangePromoCodeValueHandler}
                            />
                            <SubmitButton
                                className="!rounded-r !rounded-l-none !px-3"
                                isWithDisabledLook={hasValidationErrors(promoCodeValidationMessages)}
                                tid={TIDs.blocks_promocode_apply_button}
                                onClick={onApplyPromoCodeHandler}
                            >
                                {fetchingApplyPromoCode && <Loader className="w-4 text-white" />}
                                {t('Apply')}
                            </SubmitButton>
                        </div>
                    )}
                </>
            )}
        </div>
    );
};
