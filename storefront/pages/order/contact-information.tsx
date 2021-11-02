import { contactInformationActions, ContactInformationFormType } from 'redux/slices/contactInformation';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import ContactInformationForm from 'components/Pages/Order/ContactInformation';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import { getContactInformationFormResolver } from 'components/Pages/Order/ContactInformation/ContactInformationFormResolver';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useCreateOrder } from 'connectors/order/Order';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

const ContactInformation: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const contactInformationValues = useShopsysSelector((state) => state.contactInformation);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl, orderConfirmationUrl] = useGetInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainUrl,
    );
    const cartInput = useShopsysSelector((state) => state.cartInput);
    const t = useTypedTranslationFunction();
    const [createOrderResult, createOrder] = useCreateOrder();
    const formProviderMethods = useShopsysForm(getContactInformationFormResolver(t), contactInformationValues);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    useHandleFormSuccessfulSubmit(createOrderResult, formProviderMethods, contactInformationValues, () =>
        onSuccessfullyCreatedOrderHandler(),
    );
    useHandleFormErrors(createOrderResult.error, formProviderMethods, t('Could not create order'));
    useHandleContactInformationNonTextChanges(formProviderMethods.control, contactInformationValues);

    const onSuccessfullyCreatedOrderHandler = () => {
        const resetCartInput = initCartInputCookie();
        updateCartState(
            dispatch,
            { cart: null, transport: null, personalPickupStore: null, payment: null },
            resetCartInput,
        );
        updateCartInputCookie(resetCartInput);
        dispatch(userActions.setOrderConfirmationAccess(true));
        router.push(orderConfirmationUrl);
    };

    const onCreateOrderHandler: SubmitHandler<ContactInformationFormType> = async (formValues, event) => {
        event?.preventDefault();
        if (cartInput.transport === null || cartInput.payment === null) {
            router.replace(transportAndPaymentUrl);
            return;
        }

        dispatch(contactInformationActions.setContactInformation(formValues));

        await createOrder({
            ...formValues,
            ...{ ...cartInput, transport: cartInput.transport, payment: cartInput.payment },
            onCompanyBehalf: formValues.customer === 'companyCustomer',
            country: formValues.country.value,
            deliveryCountry: formValues.deliveryCountry.value,
        });
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <FormProvider {...formProviderMethods}>
                <form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)} noValidate>
                    <OrderLayout activeStep={3} buttonNextText={t('Submit order')}>
                        <ContactInformationForm />
                        <OrderAction
                            activeStep={3}
                            buttonBack={t('Back')}
                            buttonNext={t('Submit order')}
                            hasDisabledLook={!formProviderMethods.formState.isValid}
                            withGapTop={false}
                            withGapBottom={true}
                            buttonBackLink={transportAndPaymentUrl}
                        />
                    </OrderLayout>
                </form>
            </FormProvider>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                errors={[
                    { label: t('Your e-mail'), message: formProviderMethods.formState.errors.email?.message },
                    {
                        label: t('I want to register with an order'),
                        message: formProviderMethods.formState.errors.register?.message,
                    },
                    { label: t('Password'), message: formProviderMethods.formState.errors.passwordFirst?.message },
                    {
                        label: t('Password again'),
                        message: formProviderMethods.formState.errors.passwordSecond?.message,
                    },
                    {
                        label: t('You will shop with us like'),
                        message: formProviderMethods.formState.errors.customer?.message,
                    },
                    { label: t('Telephone'), message: formProviderMethods.formState.errors.telephone?.message },
                    { label: t('First name'), message: formProviderMethods.formState.errors.firstName?.message },
                    { label: t('Last name'), message: formProviderMethods.formState.errors.lastName?.message },
                    { label: t('Company name'), message: formProviderMethods.formState.errors.companyName?.message },
                    {
                        label: t('Company number'),
                        message: formProviderMethods.formState.errors.companyNumber?.message,
                    },
                    { label: t('Tax number'), message: formProviderMethods.formState.errors.companyTaxNumber?.message },
                    { label: t('Street'), message: formProviderMethods.formState.errors.street?.message },
                    { label: t('City'), message: formProviderMethods.formState.errors.city?.message },
                    { label: t('Postcode'), message: formProviderMethods.formState.errors.postcode?.message },
                    {
                        label: t('Enter the delivery address'),
                        message: formProviderMethods.formState.errors.differentDeliveryAddress?.message,
                    },
                    {
                        label: t('First name'),
                        message: formProviderMethods.formState.errors.deliveryFirstName?.message,
                    },
                    { label: t('Last name'), message: formProviderMethods.formState.errors.deliveryLastName?.message },
                    { label: t('Company'), message: formProviderMethods.formState.errors.deliveryCompanyName?.message },
                    { label: t('Telephone'), message: formProviderMethods.formState.errors.deliveryTelephone?.message },
                    {
                        label: t('Street and house number'),
                        message: formProviderMethods.formState.errors.deliveryStreet?.message,
                    },
                    { label: t('City'), message: formProviderMethods.formState.errors.deliveryCity?.message },
                    { label: t('Postcode'), message: formProviderMethods.formState.errors.deliveryPostcode?.message },
                    {
                        label: t('I want to subscribe to the newsletter'),
                        message: formProviderMethods.formState.errors.newsletterSubscription?.message,
                    },
                ]}
            />
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const redirect = handleOrderPagesRedirect(context);
    return redirect === false ? initServerSideProps(context, store, [NavigationQueryDocumentApi]) : redirect;
});

export default ContactInformation;
