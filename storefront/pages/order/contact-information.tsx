import {
    CreateOrderMutationApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    useCreateOrderMutationApi,
} from 'graphql/generated';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import {
    useContactInformationForm,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/formMeta';
import { contactInformationActions } from 'redux/slices/contactInformation';
import ContactInformationForm from 'components/Pages/Order/ContactInformation';
import ErrorPopup from 'components/Forms/Lib/ErrorPopup';
import { FC } from 'react';
import Footer from 'components/Layout/Footer';
import Form from 'components/Forms/Form';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import OrderAction from 'components/Blocks/OrderAction';
import OrderLayout from 'components/Layout/OrderLayout';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { updateCartState } from 'utils/Cart/UpdateCartState';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

const ContactInformation: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const contactInformationValues = useShopsysSelector((state) => state.contactInformation);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [transportAndPaymentUrl, orderConfirmationUrl] = useGetInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainUrl,
    );
    const { pickupPlace, cartInput } = useShopsysSelector((state) => state.cart);
    const t = useTypedTranslationFunction();
    const [createOrderResult, createOrder] = useCreateOrderMutationApi();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const { isUserLoggedIn } = useShopsysSelector((state) => state.user);

    const onSuccessfullyCreatedOrderHandler = (createOrderResultData: CreateOrderMutationApi | undefined) => {
        updateCartState(dispatch);
        dispatch(userActions.setOrderConfirmationAccess(true));
        dispatch(userActions.setOrderUrlHash(createOrderResultData?.CreateOrder.urlHash));
        dispatch(userActions.setLastOrderUuid(createOrderResultData?.CreateOrder.uuid ?? ''));
        router.push(orderConfirmationUrl);
    };

    useHandleFormSuccessfulSubmit(
        createOrderResult,
        formProviderMethods,
        contactInformationValues,
        onSuccessfullyCreatedOrderHandler,
    );
    useHandleFormErrors(createOrderResult.error, formProviderMethods, formMeta.messages.error);
    useHandleContactInformationNonTextChanges(formProviderMethods.control, formMeta);

    const onCreateOrderHandler: SubmitHandler<typeof defaultValues> = async (formValues, event) => {
        event?.preventDefault();
        if (cartInput.transport === null || cartInput.payment === null) {
            router.replace(transportAndPaymentUrl);
            return;
        }

        dispatch(contactInformationActions.setContactInformation(formValues));

        let deliveryInfo;

        if (pickupPlace !== null) {
            deliveryInfo = {
                deliveryFirstName: formValues.differentDeliveryAddress
                    ? formValues.deliveryFirstName
                    : formValues.firstName,
                deliveryLastName: formValues.differentDeliveryAddress
                    ? formValues.deliveryLastName
                    : formValues.lastName,
                deliveryCompanyName: formValues.differentDeliveryAddress
                    ? formValues.deliveryCompanyName
                    : formValues.companyName,
                deliveryTelephone: formValues.differentDeliveryAddress
                    ? formValues.deliveryTelephone
                    : formValues.telephone,
                deliveryStreet: formValues.differentDeliveryAddress ? formValues.deliveryStreet : pickupPlace.street,
                deliveryCity: formValues.differentDeliveryAddress ? formValues.deliveryCity : pickupPlace.city,
                deliveryPostcode: formValues.differentDeliveryAddress
                    ? formValues.deliveryPostcode
                    : pickupPlace.postcode,
                deliveryCountry: formValues.differentDeliveryAddress
                    ? formValues.deliveryCountry.value
                    : pickupPlace.country.code,
                differentDeliveryAddress: true,
            };
        } else {
            deliveryInfo = {
                deliveryFirstName: formValues.differentDeliveryAddress ? formValues.deliveryFirstName : '',
                deliveryLastName: formValues.differentDeliveryAddress ? formValues.deliveryLastName : '',
                deliveryCompanyName: formValues.differentDeliveryAddress ? formValues.deliveryCompanyName : '',
                deliveryTelephone: formValues.differentDeliveryAddress ? formValues.deliveryTelephone : '',
                deliveryStreet: formValues.differentDeliveryAddress ? formValues.deliveryStreet : '',
                deliveryCity: formValues.differentDeliveryAddress ? formValues.deliveryCity : '',
                deliveryPostcode: formValues.differentDeliveryAddress ? formValues.deliveryPostcode : '',
                deliveryCountry: formValues.differentDeliveryAddress ? formValues.deliveryCountry.value : '',
                differentDeliveryAddress: formValues.differentDeliveryAddress,
            };
        }

        await createOrder({
            ...formValues,
            ...deliveryInfo,
            ...{ ...cartInput, transport: cartInput.transport, payment: cartInput.payment },
            onCompanyBehalf: formValues.customer === 'companyCustomer',
            country: formValues.country.value,
            note: null,
        });
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
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
                </Form>
            </FormProvider>
            <Webline type={'dark'}>
                <Footer />
            </Webline>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
            />
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const cartState = store.getState().cart;
    const redirect = handleOrderPagesRedirect(context, cartState.cartInput, cartState.isCartEmpty);
    return redirect === false
        ? initServerSideProps(context, store, [
              { query: NotificationBarsDocumentApi },
              { query: NavigationQueryDocumentApi },
          ])
        : redirect;
});

export default ContactInformation;
