import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initCartInputCookie, updateCartInputCookie } from 'helpers/Cookies';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import ContactInformationForm from 'components/Pages/Order/ContactInformation';
import { ContactInformationFormType } from 'redux/slices/contactInformation';
import { FC } from 'react';
import { getContactInformationFormResolver } from 'components/Pages/Order/ContactInformation/ContactInformationFormResolver';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { navigationQuery } from 'connectors/navigation/Navigation';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useCreateOrder } from 'connectors/order/Order';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleContactInformationChanges } from 'hooks/forms/UseHandleContactInformationChanges';
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
    useHandleFormSuccessfulSubmit(createOrderResult, formProviderMethods, contactInformationValues, () =>
        onSuccessfullyCreatedOrderHandler(),
    );
    useHandleFormErrors(createOrderResult.error, formProviderMethods, t('Could not create order'));
    useHandleContactInformationChanges(formProviderMethods.control, contactInformationValues);

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

    const onCreateOrderHandler: SubmitHandler<ContactInformationFormType> = (formValues, event) => {
        event?.preventDefault();
        if (cartInput.transport === null || cartInput.payment === null) {
            router.replace(transportAndPaymentUrl);
            return;
        }

        createOrder({
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
    initDomainConfig(context, store);
    const redirect = handleOrderPagesRedirect(context);
    return redirect === false ? initServerSideProps(context, store, [navigationQuery]) : redirect;
});

export default ContactInformation;
