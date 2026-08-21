import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { PromoCodeFormType } from 'types/form';
import { useApplyPromoCodeToCart } from 'utils/cart/useApplyPromoCodeToCart';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { usePromoCodeForm, usePromoCodeFormMeta } from './promoCodeFormMeta';

type PromoCodeFormProps = {
    isContentVisible: boolean;
};

export const PromoCodeForm: FC<PromoCodeFormProps> = ({ isContentVisible }) => {
    const [formProviderMethods] = usePromoCodeForm();
    const formMeta = usePromoCodeFormMeta();
    const { t } = useTranslation();
    const { applyPromoCodeToCart } = useApplyPromoCodeToCart({
        success: t('Promo code was added to the order.'),
    });

    const onApplyPromoCodeHandler: SubmitHandler<PromoCodeFormType> = async (promoCodeFormData) => {
        blurInput();
        await applyPromoCodeToCart(promoCodeFormData.promoCode);
    };

    return (
        <div className={isContentVisible ? 'flex!' : 'hidden'}>
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
                        variant="secondary"
                    >
                        {t('Apply code')}
                    </SubmitButton>
                </Form>
            </FormProvider>
        </div>
    );
};
