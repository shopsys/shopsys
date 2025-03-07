'use client';

import { subscribeNewsletterAction } from 'app/_actions/subscribeNewsletterAction';
import {
    useNewsletterForm,
    useNewsletterFormMeta,
} from 'app/_components/Layout/Footer/NewsletterForm/newsletterFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { Form } from 'components/Forms/Form/Form';
import { ChoiceFormLine } from 'components/Forms/Lib/ChoiceFormLine';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { useTranslation } from 'components/providers/TranslationProvider';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { NewsletterFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const NewsletterForm: FC = () => {
    const { t } = useTranslation();
    const [formProviderMethods, defaultValues] = useNewsletterForm();
    const formMeta = useNewsletterFormMeta(formProviderMethods);

    const onSubscribeToNewsletterHandler: SubmitHandler<NewsletterFormType> = async (newsletterFormData) => {
        blurInput();

        const subscribeToNewsletterResult = await subscribeNewsletterAction(newsletterFormData);

        if (subscribeToNewsletterResult.error !== undefined) {
            showErrorMessage(formMeta.messages.error);
            return;
        }

        formProviderMethods.reset(defaultValues);

        showSuccessMessage(formMeta.messages.success);
    };

    return (
        <div className="flex flex-col gap-3 pt-8 pb-7 lg:flex-row lg:items-center xl:gap-10">
            <div className="text-lg font-bold break-words lg:text-2xl">
                {t('Sign up for our newsletter and get 35% discount on running apparel')}
            </div>

            <div className="lg:basis-5/12">
                <FormProvider {...formProviderMethods}>
                    <Form onSubmit={formProviderMethods.handleSubmit(onSubscribeToNewsletterHandler)}>
                        <div className="mb-2 flex flex-col lg:mb-3 lg:flex-row">
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                render={(textInput) => <FormLine>{textInput}</FormLine>}
                                textInputProps={{
                                    inputSize: 'small',
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />

                            <SubmitButton
                                className="h-12 py-0 max-lg:mt-3 lg:ml-3"
                                isWithDisabledLook={!formProviderMethods.formState.isValid}
                                variant="inverted"
                            >
                                {t('Send')}
                            </SubmitButton>
                        </div>

                        <CheckboxControlled
                            control={formProviderMethods.control}
                            formName={formMeta.formName}
                            name={formMeta.fields.privacyPolicy.name}
                            render={(checkbox) => <ChoiceFormLine className="mb-0">{checkbox}</ChoiceFormLine>}
                            checkboxProps={{
                                label: formMeta.fields.privacyPolicy.label,
                                required: true,
                            }}
                        />
                    </Form>
                </FormProvider>
            </div>
        </div>
    );
};
