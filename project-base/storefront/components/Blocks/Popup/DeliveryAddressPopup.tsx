import { SubmitButton } from 'components/Forms/Button/SubmitButton';
import { Form, FormBlockWrapper, FormButtonWrapper, FormContentWrapper } from 'components/Forms/Form/Form';
import { FormColumn } from 'components/Forms/Lib/FormColumn';
import { PhoneNumberInputControlled } from 'components/Forms/PhonePrefixSelect/PhoneNumberInputControlled';
import { CountrySelectControlled } from 'components/Forms/Select/CountrySelectControlled';
import { TextInputControlled } from 'components/Forms/TextInput/TextInputControlled';
import { Popup } from 'components/Layout/Popup/Popup';
import {
    useDeliveryAddressForm,
    useDeliveryAddressFormMeta,
} from 'components/Pages/Customer/EditProfile/deliveryAddressFormMeta';
import { TIDs } from 'cypress/tids';
import { useCreateDeliveryAddressMutation } from 'graphql/requests/customer/mutations/CreateDeliveryAddressMutation.generated';
import { useEditDeliveryAddressMutation } from 'graphql/requests/customer/mutations/EditDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { useSessionStore } from 'store/useSessionStore';
import { DeliveryAddressType } from 'types/customer';
import { DeliveryAddressFormType } from 'types/form';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { blurInput } from 'utils/forms/blurInput';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type DeliveryAddressPopupProps = {
    deliveryAddress?: DeliveryAddressType;
};

export const DeliveryAddressPopup: FC<DeliveryAddressPopupProps> = ({ deliveryAddress }) => {
    const { t } = useTranslation();
    const [, editDeliveryAddress] = useEditDeliveryAddressMutation();
    const [, createDeliveryAddress] = useCreateDeliveryAddressMutation();
    const closePortalContent = useSessionStore((s) => s.closePortalContent);

    const [formProviderMethods] = useDeliveryAddressForm({
        companyName: deliveryAddress?.companyName ?? '',
        street: deliveryAddress?.street ?? '',
        city: deliveryAddress?.city ?? '',
        postcode: deliveryAddress?.postcode ?? '',
        telephonePrefix: deliveryAddress?.telephonePrefix ?? '',
        telephonePrefixCountryCode: deliveryAddress?.telephonePrefixCountryCode ?? '',
        telephone: deliveryAddress?.telephoneNumber ?? '',
        firstName: deliveryAddress?.firstName ?? '',
        lastName: deliveryAddress?.lastName ?? '',
        country: {
            label: deliveryAddress?.country.name ?? '',
            value: deliveryAddress?.country.code ?? '',
        },
    });
    const formMeta = useDeliveryAddressFormMeta();

    const handleEditError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while editing your delivery address'),
    });

    const handleCreateError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.other,
        customMessage: t('There was an error while creating your delivery address'),
    });

    const deliveryAddressHandler: SubmitHandler<DeliveryAddressFormType> = async (deliveryAddressFormData) => {
        blurInput();

        const mutationInput = {
            firstName: deliveryAddressFormData.firstName,
            lastName: deliveryAddressFormData.lastName,
            companyName: deliveryAddressFormData.companyName,
            telephone: {
                prefix: deliveryAddressFormData.telephonePrefix,
                countryCode: deliveryAddressFormData.telephonePrefixCountryCode || '',
                number: deliveryAddressFormData.telephone,
            },
            street: deliveryAddressFormData.street,
            city: deliveryAddressFormData.city,
            postcode: deliveryAddressFormData.postcode,
            country: deliveryAddressFormData.country.value,
        };

        if (deliveryAddress?.uuid) {
            const editDeliveryAddressResult = await editDeliveryAddress({
                input: {
                    uuid: deliveryAddress.uuid,
                    ...mutationInput,
                },
            });

            closePortalContent();

            if (editDeliveryAddressResult.error !== undefined) {
                handleEditError(editDeliveryAddressResult.error);
                return;
            }

            showSuccessMessage(t('Your delivery address has been edited'));

            return;
        }

        const createDeliveryAddressResult = await createDeliveryAddress({
            input: {
                uuid: null,
                ...mutationInput,
            },
        });

        closePortalContent();

        if (createDeliveryAddressResult.error !== undefined) {
            handleCreateError(createDeliveryAddressResult.error);
            return;
        }

        showSuccessMessage(t('Your delivery address has been created'));
    };

    return (
        <Popup className="vl:w-auto w-11/12 lg:w-4/5" contentClassName="overflow-y-auto" title={t('Delivery address')}>
            <FormProvider {...formProviderMethods}>
                <Form formName={formMeta.formName} onSubmit={formProviderMethods.handleSubmit(deliveryAddressHandler)}>
                    <FormContentWrapper>
                        <FormBlockWrapper>
                            <FormColumn>
                                <TextInputControlled
                                    control={formProviderMethods.control}
                                    formName={formMeta.formName}
                                    name={formMeta.fields.firstName.name}
                                    width="half"
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
                                    width="half"
                                    textInputProps={{
                                        label: formMeta.fields.lastName.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'family-name',
                                    }}
                                />
                            </FormColumn>

                            <PhoneNumberInputControlled
                                formName={formMeta.formName}
                                formProviderMethods={formProviderMethods}
                                prefixCountryCodeName={formMeta.fields.telephonePrefixCountryCode.name}
                                prefixName={formMeta.fields.telephonePrefix.name}
                                telephoneLabel={formMeta.fields.telephone.label}
                                telephoneName={formMeta.fields.telephone.name}
                            />

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

                            <TextInputControlled
                                control={formProviderMethods.control}
                                formName={formMeta.formName}
                                name={formMeta.fields.street.name}
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
                                    width="wide"
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
                                    width="narrow"
                                    textInputProps={{
                                        label: formMeta.fields.postcode.label,
                                        required: true,
                                        type: 'text',
                                        autoComplete: 'postal-code',
                                        inputMode: 'numeric',
                                    }}
                                />
                            </FormColumn>

                            <CountrySelectControlled
                                formName={formMeta.formName}
                                formProviderMethods={formProviderMethods}
                                label={formMeta.fields.country.label}
                                name={formMeta.fields.country.name}
                            />
                        </FormBlockWrapper>

                        <FormButtonWrapper>
                            <SubmitButton
                                aria-label={t('Save. Save delivery address', { ns: 'accessibility' })}
                                tid={TIDs.delivery_address_form_submit_button}
                            >
                                {t('Save')}
                            </SubmitButton>
                        </FormButtonWrapper>
                    </FormContentWrapper>
                </Form>
            </FormProvider>
        </Popup>
    );
};
