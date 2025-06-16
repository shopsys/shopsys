import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { FormLine } from 'components/Forms/Lib/FormLine';
import { FormLineError } from 'components/Forms/Lib/FormLineError';
import { Select } from 'components/Forms/Select/Select';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import {
    useDeliveryAddressForm,
    useDeliveryAddressFormMeta,
} from 'components/Pages/Customer/EditProfile/deliveryAddressFormMeta';
import { useCreateDeliveryAddressMutation } from 'graphql/requests/customer/mutations/CreateDeliveryAddressMutation.generated';
import { useEditDeliveryAddressMutation } from 'graphql/requests/customer/mutations/EditDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import useTranslation from 'next-translate/useTranslation';
import { useEffect } from 'react';
import { Controller, FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryAddressType } from 'types/customer';
import { DeliveryAddressFormType } from 'types/form';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { blurInput } from 'utils/forms/blurInput';
import { useScrollToFirstError } from 'utils/forms/useScrollToFirstError';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type DeliveryAddressPopupProps = {
    deliveryAddress?: DeliveryAddressType;
};

export const DeliveryAddressPopup: FC<DeliveryAddressPopupProps> = ({ deliveryAddress }) => {
    const { t } = useTranslation();
    const [, editDeliveryAddress] = useEditDeliveryAddressMutation();
    const [, createDeliveryAddress] = useCreateDeliveryAddressMutation();
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const [formProviderMethods] = useDeliveryAddressForm({
        companyName: deliveryAddress?.companyName ?? '',
        street: deliveryAddress?.street ?? '',
        city: deliveryAddress?.city ?? '',
        postcode: deliveryAddress?.postcode ?? '',
        telephone: deliveryAddress?.telephone ?? '',
        firstName: deliveryAddress?.firstName ?? '',
        lastName: deliveryAddress?.lastName ?? '',
        country: {
            label: deliveryAddress?.country.name ?? '',
            value: deliveryAddress?.country.code ?? '',
        },
    });
    const formMeta = useDeliveryAddressFormMeta(formProviderMethods);
    const countriesAsSelectOptions = useCountriesAsSelectOptions();
    const { setValue } = formProviderMethods;

    useEffect(() => {
        if (countriesAsSelectOptions.length > 0 && deliveryAddress?.country) {
            const selectedCountry = countriesAsSelectOptions.find(
                (country) => country.value === deliveryAddress.country.code,
            );
            setValue(formMeta.fields.country.name, selectedCountry ?? countriesAsSelectOptions[0], {
                shouldValidate: true,
            });
        }
    }, [countriesAsSelectOptions, formMeta.fields.country.name, setValue]);

    const deliveryAddressHandler: SubmitHandler<DeliveryAddressFormType> = async (deliveryAddressFormData) => {
        blurInput();

        if (deliveryAddress?.uuid) {
            const editDeliveryAddressResult = await editDeliveryAddress({
                input: {
                    uuid: deliveryAddress.uuid,
                    ...deliveryAddressFormData,
                    country: deliveryAddressFormData.country.value,
                },
            });

            updatePortalContent(null);

            if (editDeliveryAddressResult.error !== undefined) {
                showErrorMessage(
                    t('There was an error while editing your delivery address'),
                    GtmMessageOriginType.other,
                );
                return;
            }

            showSuccessMessage(t('Your delivery address has been edited'));

            return;
        }

        const createDeliveryAddressResult = await createDeliveryAddress({
            input: {
                uuid: null,
                ...deliveryAddressFormData,
                country: deliveryAddressFormData.country.value,
            },
        });

        updatePortalContent(null);

        if (createDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while creating your delivery address'), GtmMessageOriginType.other);
            return;
        }

        showSuccessMessage(t('Your delivery address has been created'));
    };

    useScrollToFirstError(formMeta.formName, formProviderMethods);

    return (
        <Popup className="vl:w-auto w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={t('Delivery address')}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(deliveryAddressHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
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
                                name={formMeta.fields.street.name}
                                render={(textInput) => <FormLine bottomGap>{textInput}</FormLine>}
                                textInputProps={{
                                    label: formMeta.fields.street.label,
                                    required: true,
                                    type: 'text',
                                    autoComplete: 'street-address',
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
                                        inputMode: 'numeric',
                                    }}
                                />
                            </FormColumn>
                            <FormLine>
                                <Controller
                                    name={formMeta.fields.country.name}
                                    render={({ fieldState: { error }, field }) => (
                                        <>
                                            <Select
                                                isRequired
                                                ariaLabel={t('Select country')}
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
                            <FormButtonWrapper>
                                <SubmitButton aria-label={t('Submit form to save delivery address')}>
                                    {t('Save')}
                                </SubmitButton>
                            </FormButtonWrapper>
                        </FormBlockWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
