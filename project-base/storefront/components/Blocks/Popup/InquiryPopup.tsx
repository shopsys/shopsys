import { useInquiryForm, useInquiryFormMeta } from 'components/Blocks/Product/Inquiry/inquiryFormMeta';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateInquiryMutation } from 'graphql/requests/inquiry/mutations/CreateInquiryMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { InquiryFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
    const formMeta = useInquiryFormMeta();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while creating your inquiry'),
    });

    const inquiryHandler: SubmitHandler<InquiryFormType> = async (inquiryFormData) => {
        blurInput();

        const createInquiryResult = await createInquiry({
            input: {
                ...inquiryFormData,
            },
        });

        updatePortalContent(null);

        if (createInquiryResult.error !== undefined) {
            handleError(createInquiryResult.error);
            return;
        }

        showSuccessMessage(t('Your inquiry has been created'));
    };

    return (
        <Popup
            className="vl:w-auto w-11/12 overflow-x-auto lg:w-4/5"
            title={t('Inquiry')}
            ariaDescription={t(
                'This product is available on inquiry. Please fill in your information below to submit your inquiry request.',
                { ns: 'accessibility' },
            )}
        >
            <FormProvider {...formProviderMethods}>
                <Form formName={formMeta.formName} onSubmit={formProviderMethods.handleSubmit(inquiryHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
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

                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    gridClassName="col-span-2"
                                    name={formMeta.fields.firstName.name}
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
                                    gridClassName="col-span-2"
                                    name={formMeta.fields.lastName.name}
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                    }}
                                />
                            </FormColumn>

                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    gridClassName="col-span-2"
                                    name={formMeta.fields.telephone.name}
                                    textInputProps={{
                                        label: formMeta.fields.telephone.label,
                                        required: true,
                                        type: 'tel',
                                        autoComplete: 'tel',
                                    }}
                                />
                            </FormColumn>

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.companyName.name}
                                textInputProps={{
                                    label: formMeta.fields.companyName.label,
                                    type: 'text',
                                    autoComplete: 'organization',
                                }}
                            />

                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    gridClassName="col-span-2"
                                    name={formMeta.fields.companyNumber.name}
                                    textInputProps={{
                                        label: formMeta.fields.companyNumber.label,
                                        type: 'text',
                                    }}
                                />

                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    gridClassName="col-span-2"
                                    name={formMeta.fields.companyTaxNumber.name}
                                    textInputProps={{
                                        label: formMeta.fields.companyTaxNumber.label,
                                        type: 'text',
                                    }}
                                />
                            </FormColumn>

                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.note.name}
                                textareaProps={{
                                    label: formMeta.fields.note.label,
                                    rows: 4,
                                }}
                            />
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton aria-label={t('Submit form to send your inquiry', { ns: 'accessibility' })}>
                                {t('Send')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
