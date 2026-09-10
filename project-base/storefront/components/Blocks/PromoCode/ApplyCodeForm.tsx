import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form } from 'components/Forms/Form/Form';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TIDs } from 'cypress/tids';
import { AnimatePresence, m } from 'framer-motion';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { ApplyCodeFormType } from 'types/form';
import { collapseExpandAnimation } from 'utils/animations/animationVariants';
import { useApplyCodeToCart } from 'utils/cart/useApplyCodeToCart';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useApplyCodeForm, useApplyCodeFormMeta } from './applyCodeFormMeta';

type ApplyCodeFormProps = {
    isContentVisible: boolean;
};

export const ApplyCodeForm: FC<ApplyCodeFormProps> = ({ isContentVisible }) => {
    const [formProviderMethods] = useApplyCodeForm();
    const formMeta = useApplyCodeFormMeta();
    const { t } = useTranslation();
    const { applyCodeToCart } = useApplyCodeToCart();

    const onApplyCodeHandler: SubmitHandler<ApplyCodeFormType> = async (applyCodeFormData) => {
        blurInput();
        const updatedCart = await applyCodeToCart(applyCodeFormData.code);

        if (updatedCart) {
            formProviderMethods.reset({ code: '' });
        }
    };

    return (
        <AnimatePresence>
            {isContentVisible && (
                <m.div
                    key="apply-code"
                    animate="open"
                    className="flex!"
                    exit="closed"
                    id="apply-code-form"
                    initial="closed"
                    variants={collapseExpandAnimation}
                >
                    <FormProvider {...formProviderMethods}>
                        <Form
                            className="grid w-full grid-cols-1 gap-2 px-3 pt-1 pb-3 sm:grid-cols-[minmax(0,1fr)_auto] [&>div:last-child]:w-full sm:[&>div:last-child]:w-fit"
                            formName={formMeta.formName}
                            onSubmit={formProviderMethods.handleSubmit(onApplyCodeHandler)}
                        >
                            <div className="w-full min-w-0">
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.code.name}
                                    textInputProps={{
                                        inputSize: 'small',
                                        label: formMeta.fields.code.label,
                                        required: true,
                                    }}
                                />
                            </div>

                            <SubmitButton
                                aria-label={t('Apply discount coupon or gift voucher code', { ns: 'accessibility' })}
                                className="h-12 w-full whitespace-nowrap py-0 sm:w-auto"
                                hasDisabledCursor={!formProviderMethods.formState.isValid}
                                size="medium"
                                tid={TIDs.blocks_promocode_apply_button}
                            >
                                {t('Apply code')}
                            </SubmitButton>
                        </Form>
                    </FormProvider>
                </m.div>
            )}
        </AnimatePresence>
    );
};
