import { usePromoCodeForm, usePromoCodeFormMeta } from './promoCodeFormMeta';
import { Loader } from 'components/Basic/Loader/Loader';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { FormProvider } from 'react-hook-form';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useErrorPopup } from 'utils/forms/useErrorPopup';

export const PromoCode: FC = () => {
    const { promoCodes } = useCurrentCart();
    const [formProviderMethods, defaultValues] = usePromoCodeForm();
    const formMeta = usePromoCodeFormMeta(formProviderMethods);
    const { t } = useTranslation();
    const { applyPromoCodeToCart, isApplyingPromoCodeToCart } = useApplyPromoCodeToCart({
        success: t('Promo code was added to the order.'),
        error: t('There was an error while adding a promo code to the order.'),
    });
    useErrorPopup(formProviderMethods, formMeta.fields);

    const [isContentVisible, setIsContentVisible] = useState(!!defaultValues.promoCode);

    if (promoCodes.length > 0) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2.5">
            <div data-tid={TIDs.blocks_promocode_add_button}>
                <Checkbox
                    aria-expanded={isContentVisible}
                    aria-label={t('Toggle promo code')}
                    id="promo-code"
                    label={t('I have a discount coupon')}
                    value={isContentVisible}
                    onChange={() => setIsContentVisible(!isContentVisible)}
                />
            </div>
            <AnimatePresence initial={false}>
                {isContentVisible && (
                    <FormProvider {...formProviderMethods}>
                        <m.form
                            key="promo-code"
                            animate="open"
                            className="!flex flex-col gap-2.5 sm:flex-row"
                            exit="closed"
                            initial="closed"
                            variants={collapseExpandAnimation}
                            onSubmit={formProviderMethods.handleSubmit((promoCodeFormData) =>
                                applyPromoCodeToCart(promoCodeFormData.promoCode),
                            )}
                        >
                            <div className="max-w-60">
                                <TextInputControlled
                                    isWithoutFormLineError
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.promoCode.name}
                                    render={(textInput) => textInput}
                                    textInputProps={{
                                        label: formMeta.fields.promoCode.label,
                                        required: true,
                                    }}
                                />
                            </div>

                            <SubmitButton
                                aria-label={t('Submit form to apply promo code')}
                                className="self-start"
                                isWithDisabledLook={!formProviderMethods.formState.isValid}
                                size="xlarge"
                                tid={TIDs.blocks_promocode_apply_button}
                                variant="inverted"
                            >
                                {isApplyingPromoCodeToCart && <Loader className="w-4" />}

                                {t('Apply code')}
                            </SubmitButton>
                        </m.form>
                    </FormProvider>
                )}
            </AnimatePresence>
        </div>
    );
};
