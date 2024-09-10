import { Loader } from 'components/Basic/Loader/Loader';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import DropzoneControlled from 'components/Forms/Dropzone/DropzoneControlled';
import { Form, FormContentWrapper, FormBlockWrapper, FormHeading, FormButtonWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { RadiobuttonGroup } from 'components/Forms/Radiobutton/RadiobuttonGroup';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useComplaintForm, useComplaintFormMeta } from 'components/Pages/Customer/complaintFormMeta';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateComplaint } from 'graphql/requests/complaints/mutations/CreateComplaintMutation.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { ComplaintFormType } from 'types/form';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { blurInput } from 'utils/forms/blurInput';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type CreateComplaintPopupProps = {
    orderUuid: string;
    orderItem: TypeOrderDetailItemFragment;
};

export const CreateComplaintPopup: FC<CreateComplaintPopupProps> = ({ orderUuid, orderItem }) => {
    const { t } = useTranslation();
    const [, createComplaint] = useCreateComplaint();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const user = useCurrentCustomerData();

    const defaultDeliveryAddressChecked = user?.deliveryAddresses[0]?.uuid || '';
    const [formProviderMethods] = useComplaintForm(defaultDeliveryAddressChecked);
    const isSubmitting = formProviderMethods.formState.isSubmitting;
    const { setValue } = formProviderMethods;
    const formMeta = useComplaintFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0) {
            setValue(formMeta.fields.country.name, countriesAsSelectOptions[0], { shouldValidate: true });
        }
    }, [countriesAsSelectOptions, formMeta.fields.country.name, setValue]);

    const [deliveryAddressUuid] = useWatch({
        name: [formMeta.fields.deliveryAddressUuid.name],
        control: formProviderMethods.control,
    });

    const isNewDeliveryAddressSelected = deliveryAddressUuid === '';

    const createComplaintHandler: SubmitHandler<ComplaintFormType> = async (complaintFormData) => {
        blurInput();

        const items = [
            {
                orderItemUuid: orderItem.uuid,
                quantity: Number(complaintFormData.quantity),
                description: complaintFormData.description,
                files: complaintFormData.files.filter((image) => image instanceof File),
            },
        ];

        const deliveryAddress =
            user?.deliveryAddresses && user.deliveryAddresses.length > 0 && !isNewDeliveryAddressSelected
                ? {
                      uuid: null,
                      firstName: user.deliveryAddresses[0].firstName,
                      lastName: user.deliveryAddresses[0].lastName,
                      companyName: user.deliveryAddresses[0].companyName,
                      street: user.deliveryAddresses[0].street,
                      city: user.deliveryAddresses[0].city,
                      postcode: user.deliveryAddresses[0].postcode,
                      telephone: user.deliveryAddresses[0].telephone,
                      country: user.deliveryAddresses[0].country.code,
                  }
                : {
                      uuid: null,
                      firstName: complaintFormData.firstName,
                      lastName: complaintFormData.lastName,
                      companyName: complaintFormData.companyName,
                      street: complaintFormData.street,
                      city: complaintFormData.city,
                      postcode: complaintFormData.postcode,
                      telephone: complaintFormData.telephone,
                      country: complaintFormData.country.value,
                  };

        const createComplaintResult = await createComplaint({
            input: {
                orderUuid,
                items,
                deliveryAddress,
            },
        });

        if (createComplaintResult.error !== undefined) {
            const { applicationError } = getUserFriendlyErrors(createComplaintResult.error, t);
            if (applicationError !== undefined) {
                showErrorMessage(applicationError.message, GtmMessageOriginType.other);
            } else {
                showErrorMessage(t('There was an error while creating your complaint'), GtmMessageOriginType.other);
            }

            return;
        }

        updatePortalContent(null);

        showSuccessMessage(t('Complaint has been created'));
    };

    return (
        <Popup className="w-11/12 lg:w-4/5 overflow-x-auto">
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(createComplaintHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <FormHeading>{t('Create complaint')}</FormHeading>
                            <h5 className="mb-2">{orderItem.name}</h5>
                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.quantity.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.quantity.label,
                                    required: true,
                                    type: 'number',
                                    autoComplete: 'quantity',
                                    disabled: isSubmitting,
                                }}
                            />
                            <TextareaControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.description.name}
                                render={(textarea) => <FormLine>{textarea}</FormLine>}
                                textareaProps={{
                                    label: formMeta.fields.description.label,
                                    rows: 3,
                                    required: true,
                                    disabled: isSubmitting,
                                }}
                            />
                        </FormBlockWrapper>
                        <FormBlockWrapper>
                            <FormHeading>{t('Attachments')}</FormHeading>
                            <DropzoneControlled
                                required
                                control={formProviderMethods.control}
                                disabled={isSubmitting}
                                formName={formMeta.formName}
                                label={t('Drag & drop some files here, or click to select files')}
                                name={formMeta.fields.files.name}
                                render={(dropzone) => <FormLine>{dropzone}</FormLine>}
                            />
                        </FormBlockWrapper>
                        <FormBlockWrapper>
                            <FormHeading>{t('Delivery address')}</FormHeading>
                            <div className="flex w-full flex-col space-y-5 ">
                                <RadiobuttonGroup
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.deliveryAddressUuid.name}
                                    radiobuttons={
                                        user!.deliveryAddresses.length > 0
                                            ? [
                                                  ...user!.deliveryAddresses.map((deliveryAddress) => ({
                                                      label: (
                                                          <p className="flex flex-col">
                                                              <strong className="mr-1">
                                                                  {deliveryAddress.firstName} {deliveryAddress.lastName}
                                                              </strong>
                                                              <span>{deliveryAddress.companyName}</span>
                                                              <span>{deliveryAddress.telephone}</span>
                                                              <span>
                                                                  {deliveryAddress.street}, {deliveryAddress.city},{' '}
                                                                  {deliveryAddress.postcode}
                                                              </span>
                                                              <span>{deliveryAddress.country.name}</span>
                                                          </p>
                                                      ),
                                                      value: deliveryAddress.uuid,
                                                      labelWrapperClassName: 'flex-row-reverse',
                                                      disabled: isSubmitting,
                                                  })),
                                                  {
                                                      label: (
                                                          <p>
                                                              <span className="font-bold">
                                                                  {t('Different delivery address')}
                                                              </span>
                                                          </p>
                                                      ),
                                                      value: '',
                                                      id: 'new-delivery-address',
                                                      labelWrapperClassName: 'flex-row-reverse',
                                                      disabled: isSubmitting,
                                                  },
                                              ]
                                            : []
                                    }
                                    render={(radiobutton, key) => (
                                        <div
                                            key={key}
                                            className="relative flex w-full flex-wrap rounded p-5 bg-background border-2 border-borderAccent"
                                        >
                                            {radiobutton}
                                        </div>
                                    )}
                                />
                            </div>
                            {isNewDeliveryAddressSelected && (
                                <>
                                    <FormColumn className="mt-4">
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
                                                disabled: isSubmitting,
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
                                                disabled: isSubmitting,
                                            }}
                                        />
                                    </FormColumn>
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.companyName.name}
                                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                        textInputProps={{
                                            label: formMeta.fields.companyName.label,
                                            type: 'text',
                                            autoComplete: 'organization',
                                            disabled: isSubmitting,
                                        }}
                                    />
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
                                            disabled: isSubmitting,
                                        }}
                                    />
                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.street.name}
                                        render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                        textInputProps={{
                                            label: formMeta.fields.street.label,
                                            required: true,
                                            type: 'text',
                                            autoComplete: 'street-address',
                                            disabled: isSubmitting,
                                        }}
                                    />
                                    <FormColumn>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.city.name}
                                            render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                            textInputProps={{
                                                label: formMeta.fields.city.label,
                                                required: true,
                                                type: 'text',
                                                autoComplete: 'address-level2',
                                                disabled: isSubmitting,
                                            }}
                                        />
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.postcode.name}
                                            render={(textInput) => (
                                                <FormLine bottomGap isSmallInput>
                                                    {textInput}
                                                </FormLine>
                                            )}
                                            textInputProps={{
                                                label: formMeta.fields.postcode.label,
                                                required: true,
                                                type: 'text',
                                                autoComplete: 'postal-code',
                                                disabled: isSubmitting,
                                            }}
                                        />
                                    </FormColumn>
                                    <FormLine>
                                        <Controller
                                            name={formMeta.fields.country.name}
                                            render={({ fieldState: { invalid, error }, field }) => (
                                                <>
                                                    <Select
                                                        hasError={invalid}
                                                        isDisabled={isSubmitting}
                                                        label={formMeta.fields.country.label}
                                                        options={countriesAsSelectOptions}
                                                        value={countriesAsSelectOptions.find(
                                                            (option) => option.value === field.value.value,
                                                        )}
                                                        onChange={field.onChange}
                                                    />
                                                    <FormLineError error={error} inputType="select" />
                                                </>
                                            )}
                                        />
                                    </FormLine>
                                </>
                            )}

                            <FormButtonWrapper>
                                <SubmitButton>
                                    {isSubmitting ? (
                                        <>
                                            <Loader className="w-4 h-4" /> {t('Sending...')}
                                        </>
                                    ) : (
                                        t('Send complaint')
                                    )}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
