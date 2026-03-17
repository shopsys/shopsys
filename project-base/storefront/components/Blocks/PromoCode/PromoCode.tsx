import { usePromoCodeForm, usePromoCodeFormMeta } from './promoCodeFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import { useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PromoCodeFormType } from 'types/form';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const PromoCode: FC = () => {
    const { promoCodes } = useCurrentCart();
    const [formProviderMethods, defaultValues] = usePromoCodeForm();
    const formMeta = usePromoCodeFormMeta();
    const { t } = useTranslation();
    const { applyPromoCodeToCart } = useApplyPromoCodeToCart({
        success: t('Promo code was added to the order.'),
    });

    const [isContentVisible, setIsContentVisible] = useState(!!defaultValues.promoCode);

    const onApplyPromoCodeHandler: SubmitHandler<PromoCodeFormType> = async (promoCodeFormData) => {
        blurInput();
        await applyPromoCodeToCart(promoCodeFormData.promoCode);
    };

    if (promoCodes.length > 0) {
        return null;
    }

    return (
        <div className="flex flex-col gap-2.5">
            <div data-tid={TIDs.blocks_promocode_add_button}>
                <Checkbox
                    aria-expanded={isContentVisible}
                    aria-label={t('Toggle promo code', { ns: 'accessibility' })}
                    id="promo-code"
                    label={t('I have a discount coupon')}
                    value={isContentVisible}
                    onChange={() => setIsContentVisible(!isContentVisible)}
                />
            </div>
            <AnimatePresence initial={false}>
                {isContentVisible && (
                    <m.div
                        key="promo-code"
                        animate="open"
                        className="flex!"
                        exit="closed"
                        initial="closed"
                        variants={collapseExpandAnimation}
                    >
                        <FormProvider {...formProviderMethods}>
                            <Form
                                className="flex flex-col gap-2.5 sm:flex-row"
                                formName={formMeta.formName}
                                onSubmit={formProviderMethods.handleSubmit(onApplyPromoCodeHandler)}
                            >
                                <div className="max-w-60">
                                    <TextInputControlled
                                        isWithoutFormLineError
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.promoCode.name}
                                        textInputProps={{
                                            label: formMeta.fields.promoCode.label,
                                            required: true,
                                        }}
                                    />
                                </div>

                                <SubmitButton
                                    aria-label={t('Submit form to apply promo code', { ns: 'accessibility' })}
                                    className="self-start"
                                    hasDisabledCursor={!formProviderMethods.formState.isValid}
                                    size="xlarge"
                                    tid={TIDs.blocks_promocode_apply_button}
                                    variant="inverted"
                                >
                                    {t('Apply code')}
                                </SubmitButton>
                            </Form>
                        </FormProvider>
                    </m.div>
                )}
            </AnimatePresence>
        </div>
    );
};
