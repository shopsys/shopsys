import { AddressList } from 'components/Blocks/AddressList/AddressList';
import { Button } from 'components/Forms/Button/Button';
import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { DropzoneControlled } from 'components/Forms/Dropzone/DropzoneControlled';
import { Form, FormContentWrapper, FormBlockWrapper, FormHeading, FormButtonWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { TextareaControlled } from 'components/Forms/Textarea/TextareaControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import { useComplaintForm, useComplaintFormMeta } from 'components/Pages/Customer/Complaints/complaintFormMeta';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { useCreateComplaint } from 'graphql/requests/complaints/mutations/CreateComplaintMutation.generated';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { Controller, FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { ComplaintFormType } from 'types/form';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { useComplaintResolutionsAsSelectOptions } from 'utils/complaints/useComplaintResolutionsAsSelectOptions';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type CreateComplaintPopupProps = {
    orderUuid?: string;
    orderItem?: TypeOrderDetailItemFragment;
};

export const CreateComplaintPopup: FC<CreateComplaintPopupProps> = ({ orderUuid = null, orderItem = null }) => {
    const router = useRouter();
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerComplaintsUrl] = getInternationalizedStaticUrls(['/customer/complaints'], url);
    const [, createComplaint] = useCreateComplaint();
    const user = useCurrentCustomerData();

    const defaultDeliveryAddressChecked = user?.defaultDeliveryAddress?.uuid || user?.deliveryAddresses[0]?.uuid || '';
    const isCreationWithoutOrder = orderUuid === null;
    const [formProviderMethods] = useComplaintForm(
        defaultDeliveryAddressChecked,
        user?.email || '',
        isCreationWithoutOrder,
    );
    const isSubmitting = formProviderMethods.formState.isSubmitting;
    const { setValue } = formProviderMethods;
    const formMeta = useComplaintFormMeta(formProviderMethods);
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while creating your complaint'),
    });
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const complaintResolutionsAsOptions = useComplaintResolutionsAsSelectOptions();
    const [showNewAddressForm, setShowNewAddressForm] = useState(false);

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0) {
            setValue(formMeta.fields.country.name, countriesAsSelectOptions[0], { shouldValidate: true });
        }
    }, [countriesAsSelectOptions, formMeta.fields.country.name, setValue]);

    const [deliveryAddressUuid, resolution] = useWatch({
        name: [formMeta.fields.deliveryAddressUuid.name, formMeta.fields.resolution.name],
        control: formProviderMethods.control,
    });

    const isNewDeliveryAddressSelected = deliveryAddressUuid === '';
    const isMoneyBackResolutionSelected = isResolutionMoneyReturn(resolution);

    const handleChangeDeliveryAddressForComplaint = (value: string) => {
        setValue(formMeta.fields.deliveryAddressUuid.name, value);
        setShowNewAddressForm(false);
    };

    const handleShowNewAddressForm = () => {
        setValue(formMeta.fields.deliveryAddressUuid.name, '');
        setShowNewAddressForm(true);
    };

    if (deliveryAddressUuid === '' && !showNewAddressForm) {
        setShowNewAddressForm(true);
    }

    const createComplaintHandler: SubmitHandler<ComplaintFormType> = async (complaintFormData) => {
        blurInput();

        const items = [
            {
                orderItemUuid: orderItem?.uuid ?? null,
                quantity: Number(complaintFormData.quantity),
                description: complaintFormData.description,
                files: complaintFormData.files.filter((image) => image instanceof File),
                manualComplaintItemName: complaintFormData.manualComplaintItemName,
                manualComplaintItemCatnum: complaintFormData.manualComplaintItemCatnum,
            },
        ];

        const selectedDeliveryAddress =
            user?.deliveryAddresses &&
            user.deliveryAddresses.find((address) => address.uuid === complaintFormData.deliveryAddressUuid);

        const deliveryAddress =
            selectedDeliveryAddress && !isNewDeliveryAddressSelected
                ? {
                      uuid: null,
                      firstName: selectedDeliveryAddress.firstName,
                      lastName: selectedDeliveryAddress.lastName,
                      companyName: selectedDeliveryAddress.companyName,
                      street: selectedDeliveryAddress.street,
                      city: selectedDeliveryAddress.city,
                      postcode: selectedDeliveryAddress.postcode,
                      telephone: selectedDeliveryAddress.telephone,
                      country: selectedDeliveryAddress.country.code,
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
                email: complaintFormData.email,
                manualDocumentNumber: complaintFormData.manualDocumentNumber,
                resolution: complaintFormData.resolution.value,
                bankAccountNumber: complaintFormData.bankAccountNumber ?? null,
            },
        });

        if (createComplaintResult.error !== undefined) {
            handleError(createComplaintResult.error);

            return;
        }

        router.replace(customerComplaintsUrl);

        showSuccessMessage(t('Complaint has been created'));
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <Popup className="w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={t('Create complaint')}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(createComplaintHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            {isCreationWithoutOrder && (
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.manualDocumentNumber.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.manualDocumentNumber.label,
                                        required: true,
                                        type: 'text',
                                        disabled: isSubmitting,
                                    }}
                                />
                            )}

                            <p className="h5">{orderItem?.name ?? t('Complaint item')}</p>

                            {isCreationWithoutOrder && (
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.manualComplaintItemName.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.manualComplaintItemName.label,
                                        required: true,
                                        type: 'text',
                                        disabled: isSubmitting,
                                    }}
                                />
                            )}

                            {isCreationWithoutOrder && (
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.manualComplaintItemCatnum.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.manualComplaintItemCatnum.label,
                                        required: false,
                                        type: 'text',
                                        disabled: isSubmitting,
                                    }}
                                />
                            )}

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.quantity.name}
                                render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                            <FormHeading>{t('Email')}</FormHeading>

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.email.name}
                                render={(textInput) => <FormLine>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.email.label,
                                    required: true,
                                    type: 'email',
                                    autoComplete: 'email',
                                }}
                            />
                        </FormBlockWrapper>

                        <FormBlockWrapper>
                            <FormHeading>{t('Resolution')}</FormHeading>

                            <Controller
                                name={formMeta.fields.resolution.name}
                                render={({ fieldState: { error }, field }) => (
                                    <FormLine>
                                        <Select
                                            isRequired
                                            ariaLabel={t('Select resolution', { ns: 'accessibility' })}
                                            isDisabled={isSubmitting}
                                            label={formMeta.fields.resolution.label}
                                            options={complaintResolutionsAsOptions}
                                            tid={formMeta.formName + '-' + formMeta.fields.resolution.name}
                                            activeOption={complaintResolutionsAsOptions.find(
                                                (option) => option.value === field.value.value,
                                            )}
                                            onSelectOption={field.onChange}
                                        />
                                        <FormLineError error={error} inputType="select" />
                                    </FormLine>
                                )}
                            />

                            {isMoneyBackResolutionSelected && (
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.bankAccountNumber.name}
                                    render={(textInput) => <FormLine>{textInput}</FormLine>}
                                    textInputProps={{
                                        label: formMeta.fields.bankAccountNumber.label,
                                        required: true,
                                        type: 'email',
                                        autoComplete: 'email',
                                    }}
                                />
                            )}
                        </FormBlockWrapper>

                        <FormBlockWrapper>
                            <FormHeading>{t('Delivery address')}</FormHeading>

                            {user && user.deliveryAddresses.length > 0 && !showNewAddressForm && (
                                <>
                                    <AddressList
                                        hideAddAddressAction
                                        defaultDeliveryAddress={user.defaultDeliveryAddress}
                                        deliveryAddresses={user.deliveryAddresses}
                                        orderSelectAddressHandler={handleChangeDeliveryAddressForComplaint}
                                        orderSelectedAddress={deliveryAddressUuid ?? undefined}
                                    />

                                    <Button
                                        className="self-start"
                                        size="small"
                                        variant="secondary"
                                        onClick={handleShowNewAddressForm}
                                    >
                                        {t('Add different delivery address')}
                                    </Button>
                                </>
                            )}

                            {(showNewAddressForm || !user || user.deliveryAddresses.length === 0) && (
                                <>
                                    {user && user.deliveryAddresses.length > 0 && (
                                        <Button
                                            className="self-start"
                                            size="small"
                                            variant="secondary"
                                            onClick={() => {
                                                setShowNewAddressForm(false);
                                                setValue(
                                                    formMeta.fields.deliveryAddressUuid.name,
                                                    user.defaultDeliveryAddress?.uuid ||
                                                        user.deliveryAddresses[0]?.uuid ||
                                                        '',
                                                );
                                            }}
                                        >
                                            {t('Select from existing addresses')}
                                        </Button>
                                    )}

                                    <FormColumn>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.firstName.name}
                                            render={(textInput) => (
                                                <FormLine className="col-span-2">{textInput}</FormLine>
                                            )}
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
                                            render={(textInput) => (
                                                <FormLine className="col-span-2">{textInput}</FormLine>
                                            )}
                                            textInputProps={{
                                                label: formMeta.fields.lastName.label,
                                                required: true,
                                                type: 'text',
                                                autoComplete: 'family-name',
                                                disabled: isSubmitting,
                                            }}
                                        />
                                    </FormColumn>

                                    <FormColumn>
                                        <TextInputControlled
                                            control={formProviderMethods.control}
                                            formName={formMeta.formName}
                                            name={formMeta.fields.telephone.name}
                                            render={(textInput) => (
                                                <FormLine className="col-span-2">{textInput}</FormLine>
                                            )}
                                            textInputProps={{
                                                label: formMeta.fields.telephone.label,
                                                required: true,
                                                type: 'tel',
                                                autoComplete: 'tel',
                                                disabled: isSubmitting,
                                            }}
                                        />
                                    </FormColumn>

                                    <TextInputControlled
                                        control={formProviderMethods.control}
                                        formName={formMeta.formName}
                                        name={formMeta.fields.companyName.name}
                                        render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                                        name={formMeta.fields.street.name}
                                        render={(textInput) => <FormLine>{textInput}</FormLine>}
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
                                            render={(textInput) => (
                                                <FormLine className="col-span-3">{textInput}</FormLine>
                                            )}
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
                                                <FormLine className="col-start-4">{textInput}</FormLine>
                                            )}
                                            textInputProps={{
                                                label: formMeta.fields.postcode.label,
                                                required: true,
                                                type: 'text',
                                                autoComplete: 'postal-code',
                                                disabled: isSubmitting,
                                                inputMode: 'numeric',
                                            }}
                                        />
                                    </FormColumn>

                                    <FormColumn>
                                        <FormLine className="col-span-3">
                                            <Controller
                                                name={formMeta.fields.country.name}
                                                render={({ fieldState: { error }, field }) => (
                                                    <>
                                                        <Select
                                                            isRequired
                                                            ariaLabel={t('Select country', { ns: 'accessibility' })}
                                                            isDisabled={isSubmitting}
                                                            label={formMeta.fields.country.label}
                                                            options={countriesAsSelectOptions}
                                                            tid={formMeta.formName + '-' + formMeta.fields.country.name}
                                                            activeOption={countriesAsSelectOptions.find(
                                                                (option) => option.value === field.value.value,
                                                            )}
                                                            onSelectOption={field.onChange}
                                                        />
                                                        <FormLineError error={error} inputType="select" />
                                                    </>
                                                )}
                                            />
                                        </FormLine>
                                    </FormColumn>
                                </>
                            )}
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton
                                aria-label={t('Submit your complaint', { ns: 'accessibility' })}
                                data-tid={TIDs.complaint_create_submit_button}
                            >
                                {t('Send complaint')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
