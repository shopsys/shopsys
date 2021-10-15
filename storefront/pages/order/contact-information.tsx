import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import ContactInformationForm from 'components/Pages/ContactInformation';
import { FC } from 'react';
import { FormProvider } from 'react-hook-form';
import { getContactInformationFormResolver } from 'components/Pages/ContactInformation/ContactInformationFormResolver';
import { navigationQuery } from 'connectors/navigation/Navigation';
import { nextReduxWrapper } from 'redux/main';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { TFunction } from 'next-i18next';
import { useCreateOrder } from 'connectors/order/Order';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useHandleFormValidationErrors } from 'hooks/forms/UseHandleFormValidationErrors';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export const getCountrySelectOptions = (t: TFunction): { value: string; label: string }[] => [
    { value: 'SK', label: t('Slovakia') },
    { value: 'CZ', label: t('Czech Republic') },
];

const getContactInformationFormDefaultValues = (t: TFunction) => {
    return {
        email: '',
        register: false,
        passwordFirst: '',
        passwordSecond: '',
        customer: 'commonCustomer',
        telephone: '',
        firstName: '',
        lastName: '',
        street: '',
        city: '',
        postcode: '',
        country: getCountrySelectOptions(t)[0].value,
        companyName: '',
        companyNumber: '',
        companyTaxNumber: '',
        differentDeliveryAddress: false,
        deliveryFirstName: '',
        deliveryLastName: '',
        deliveryCompanyName: '',
        deliveryTelephone: '',
        deliveryStreet: '',
        deliveryCity: '',
        deliveryPostcode: '',
        deliveryCountry: getCountrySelectOptions(t)[0].value,
        newsletterSubscription: false,
    };
};

const ContactInformation: FC<ServerSidePropsType> = (props) => {
    const cartInput = useShopsysSelector((state) => state.cartInput);
    useInitDomainConfig(props.domainConfig);
    const t = useTypedTranslationFunction();
    const [createOrderResult, createOrder] = useCreateOrder();
    const formProviderMethods = useShopsysForm(
        getContactInformationFormResolver(t),
        getContactInformationFormDefaultValues(t),
    );
    useHandleFormSuccessfulSubmit(
        createOrderResult,
        formProviderMethods,
        getContactInformationFormDefaultValues(t),
        undefined,
    );
    useHandleFormValidationErrors(createOrderResult.error, formProviderMethods);

    const onCreateOrderHandler: SubmitHandler<ReturnType<typeof getContactInformationFormDefaultValues>> = (
        formValues,
        event,
    ) => {
        event?.preventDefault();
        if (cartInput.transport === null) {
            showErrorMessage('Transport is null');
            return;
        }
        if (cartInput.payment === null) {
            showErrorMessage('Payment is null');
            return;
        }

        createOrder({
            ...formValues,
            ...{ ...cartInput, transport: cartInput.transport, payment: cartInput.payment },
            onCompanyBehalf: formValues.customer === 'companyCustomer',
        });
    };

    return (
        <StaticUrlGuard domainUrl={props.domainConfig.url}>
            <FormProvider {...formProviderMethods}>
                <form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
                    <ContactInformationForm />
                </OrderLayout>
                </form>
            </FormProvider>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return initServerSideProps(context, store, [navigationQuery]);
});

export default ContactInformation;
