import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PromoCodeFormType } from 'types/form';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { usePromoCodeForm, usePromoCodeFormMeta } from './promoCodeFormMeta';

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
                    data-tid={TIDs.blocks_promocode_add_button}
                    label={t('I have a discount coupon')}
                    value={isContentVisible}
                    onChange={() => setIsContentVisible(!isContentVisible)}
                />
            </div>
            {isContentVisible && (
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
                            aria-label={t('Apply code. Apply promo code', { ns: 'accessibility' })}
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
            )}
        </div>
    );
};
