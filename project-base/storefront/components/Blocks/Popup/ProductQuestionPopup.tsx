import {
    useProductQuestionForm,
    useProductQuestionFormMeta,
} from 'components/Blocks/Product/Question/productQuestionFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useProductQuestionMutation } from 'graphql/requests/productQuestion/mutations/ProductQuestionMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { ProductQuestionFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type ProductQuestionPopupProps = {
    productUuid: string;
};

export const ProductQuestionPopup: FC<ProductQuestionPopupProps> = ({ productUuid }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const user = useCurrentCustomerData();
    const [, sendProductQuestion] = useProductQuestionMutation();

    const [formProviderMethods] = useProductQuestionForm({
        customerName: [user?.firstName, user?.lastName].filter(Boolean).join(' '),
        email: user?.email ?? '',
        question: '',
        productUuid,
    });
    const formMeta = useProductQuestionFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while sending your question'),
    });

    const productQuestionHandler: SubmitHandler<ProductQuestionFormType> = async (productQuestionFormData) => {
        blurInput();

        const sendProductQuestionResult = await sendProductQuestion({
            input: {
                customerName: productQuestionFormData.customerName,
                email: productQuestionFormData.email,
                question: productQuestionFormData.question,
                productUuid: productQuestionFormData.productUuid,
            },
        });

        if (sendProductQuestionResult.error !== undefined) {
            handleError(sendProductQuestionResult.error);
            return;
        }

        if (sendProductQuestionResult.data?.ProductQuestion !== true) {
            showErrorMessage(t('There was an error while sending your question'), GtmMessageOriginType.other);
            return;
        }

        updatePortalContent(null);
        showSuccessMessage(t('Your question has been sent'));
    };

    return (
        <Popup
            className="w-11/12 lg:w-1/2"
            title={t('Ask a product question')}
            ariaDescription={t('Fill in your name, email and question and we will get back to you by email.', {
                ns: 'accessibility',
            })}
        >
            <FormProvider {...formProviderMethods}>
                <Form formName={formMeta.formName} onSubmit={formProviderMethods.handleSubmit(productQuestionHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.customerName.name}
                                textInputProps={{
                                    label: formMeta.fields.customerName.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'name',
                                }}
                            />

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />

                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.question.name}
                                textareaProps={{
                                    label: formMeta.fields.question.label,
                                    required: true,
                                    rows: 5,
                                }}
                            />
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton>{t('Send')}</SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
