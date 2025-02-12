import { useInquiryForm } from 'components/Blocks/Product/Inquiry/inquiryFormMeta';
import { useInquiryFormMeta } from 'components/Blocks/Product/Inquiry/inquiryFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateInquiryMutation } from 'graphql/requests/inquiry/mutations/CreateInquiryMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { InquiryFormType } from 'types/form';
import { blurInput } from 'utils/forms/blurInput';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type InquiryPopupProps = {
    productUuid: string;
};

export const InquiryPopup: FC<InquiryPopupProps> = ({ productUuid }) => {
    const { t } = useTranslation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const user = useCurrentCustomerData();
    const [, createInquiry] = useCreateInquiryMutation();

    const [formProviderMethods] = useInquiryForm({
        email: user?.email ?? '',
        firstName: user?.firstName ?? '',
        lastName: user?.lastName ?? '',
        telephone: user?.telephone ?? '',
        companyName: user?.companyName ?? '',
        companyNumber: user?.companyNumber ?? '',
        companyTaxNumber: user?.companyTaxNumber ?? '',
        note: '',
        productUuid,
    });
    const formMeta = useInquiryFormMeta(formProviderMethods);

    const inquiryHandler: SubmitHandler<InquiryFormType> = async (inquiryFormData) => {
        blurInput();

        const createInquiryResult = await createInquiry({
            input: {
                ...inquiryFormData,
            },
        });

        updatePortalContent(null);

        if (createInquiryResult.error !== undefined) {
            showErrorMessage(t('There was an error while creating your inquiry'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('Your inquiry has been created'));
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <Popup
            className="vl:w-auto w-11/12 overflow-x-auto lg:w-4/5"
            title={t('Inquiry')}
            ariaDescription={t(
                'This product is available on inquiry. Please fill in your information below to submit your inquiry request.',
            )}
        >
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(inquiryHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.email.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.email.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                            </FormColumn>

                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.firstName.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.firstName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'given-name',
                                    }}
                                />
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.lastName.name}
                                    render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                    }}
                                />
                            </FormColumn>

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.telephone.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.telephone.label,
                                    required: true,
                                    type: 'tel',
                                    autoComplete: 'tel',
                                }}
                            />

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.companyName.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.companyName.label,
                                    type: 'text',
                                    autoComplete: 'organization',
                                }}
                            />

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.companyNumber.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.companyNumber.label,
                                    type: 'text',
                                }}
                            />

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.companyTaxNumber.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.companyTaxNumber.label,
                                    type: 'text',
                                }}
                            />

                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.note.name}
                                render={(textarea) => <FormLine>{textarea}</FormLine>}
                                textareaProps={{
                                    label: formMeta.fields.note.label,
                                    rows: 4,
                                }}
                            />

                            <FormButtonWrapper>
                                <SubmitButton aria-label={t('Submit form to send your inquiry')}>
                                    {t('Send')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
