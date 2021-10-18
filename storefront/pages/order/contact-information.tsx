import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import ContactInformationForm from 'components/Pages/Order/ContactInformation';
import { FC } from 'react';
import { getContactInformationFormResolver } from 'components/Pages/Order/ContactInformation/ContactInformationFormResolver';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import { OrderApiType } from 'connectors/order/types';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { TFunction } from 'next-i18next';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useCreateOrder } from 'connectors/order/Order';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useHandleFormValidationErrors } from 'hooks/forms/UseHandleFormValidationErrors';
import { useInitDomainConfig } from 'hooks/helpers/UseInitDomainConfig';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
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
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const { url } = useShopsysSelector((state) => state.domain);
    const [transportAndPaymentUrl, orderConfirmationUrl] = useGetInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        url,
    );
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
        (resultData) => onSuccessfullyCreatedOrderHandler(resultData),
    );
    useHandleFormValidationErrors(createOrderResult.error, formProviderMethods);

    const onSuccessfullyCreatedOrderHandler = (resultData: { CreateOrder: OrderApiType }) => {
        const resetCartInput = initCartInputCookie();
        updateCartState(
            dispatch,
            { cart: null, transport: null, personalPickupStore: null, payment: null },
            resetCartInput,
        );
        updateCartInputCookie(resetCartInput);
        dispatch(userActions.setEmail(resultData.CreateOrder.email));
        router.push(orderConfirmationUrl);
    };

    const onCreateOrderHandler: SubmitHandler<ReturnType<typeof getContactInformationFormDefaultValues>> = (
        formValues,
        event,
    ) => {
        event?.preventDefault();
        if (cartInput.transport === null || cartInput.payment === null) {
            router.replace(transportAndPaymentUrl);
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
                    <OrderLayout activeStep={3} buttonNextText={t('Submit order')}>
                        <ContactInformationForm />
                        <OrderAction
                            activeStep={3}
                            buttonBack={t('Back')}
                            buttonNext={t('Submit order')}
                            isDisabled={!formProviderMethods.formState.isValid}
                            withGapTop={false}
                            withGapBottom={true}
                            buttonBackLink={transportAndPaymentUrl}
                        />
                    </OrderLayout>
                </form>
            </FormProvider>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    return handleOrderPagesRedirect(context) || initServerSideProps(context, store, [navigationQuery]);
});

export default ContactInformation;
