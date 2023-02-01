import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { EmptyCartWrapper } from 'components/Pages/Cart/EmptyCartWrapper';
import { ContactInformationContent } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import {
    useContactInformationForm,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/formMeta';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { useCreateOrderMutationApi } from 'graphql/generated';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { onPurchaseOrderGtmEventHandler } from 'helpers/gtm/eventHandlers';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useGtmShippingDataView } from 'hooks/gtm/useGtmShippingDataView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import React, { FC, useCallback, useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { userActions } from 'redux/slices/user';

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [transportAndPaymentUrl, orderConfirmationUrl] = getInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainUrl,
    );
    const [orderCreating, setOrderCreating] = useState(false);
    const currentCart = useCurrentCart();
    const [changePaymentInCart] = useChangePaymentInCart();
    const t = useTypedTranslationFunction();
    const [{ fetching }, createOrder] = useCreateOrderMutationApi();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('shipping data');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmShippingDataView(
        currentCart.transport,
        currentCart.pickupPlace,
        currentCart.payment?.name,
        gtmStaticPageViewEvent,
    );
    useHandleContactInformationNonTextChanges(formProviderMethods.control, formMeta);

    const onCreateOrderHandler = useCallback<SubmitHandler<typeof defaultValues>>(
        async (formValues) => {
            setOrderCreating(true);
            dispatch(contactInformationActions.setContactInformation(formValues));

            let deliveryInfo;

            if (currentCart.pickupPlace !== null) {
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
                    deliveryStreet: formValues.differentDeliveryAddress
                        ? formValues.deliveryStreet
                        : currentCart.pickupPlace.street,
                    deliveryCity: formValues.differentDeliveryAddress
                        ? formValues.deliveryCity
                        : currentCart.pickupPlace.city,
                    deliveryPostcode: formValues.differentDeliveryAddress
                        ? formValues.deliveryPostcode
                        : currentCart.pickupPlace.postcode,
                    deliveryCountry: formValues.differentDeliveryAddress
                        ? formValues.deliveryCountry.value
                        : currentCart.pickupPlace.country.code,
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

            const createOrderResult = await createOrder({
                cartUuid,
                ...formValues,
                ...deliveryInfo,
                deliveryAddressUuid: formValues.deliveryAddressUuid !== '' ? formValues.deliveryAddressUuid : null,
                onCompanyBehalf: formValues.customer === 'companyCustomer',
                country: formValues.country.value,
            });

            if (
                createOrderResult.data !== undefined &&
                createOrderResult.data.CreateOrder.orderCreated === true &&
                createOrderResult.data.CreateOrder.order !== null &&
                currentCart.cart !== null &&
                currentCart.transport !== null &&
                currentCart.payment !== null
            ) {
                onPurchaseOrderGtmEventHandler(
                    currentCart.cart,
                    currentCart.transport,
                    currentCart.pickupPlace,
                    currentCart.payment,
                    currentCart.promoCode,
                    createOrderResult.data.CreateOrder.order.number,
                    domainUrl,
                );

                dispatch(userActions.setCartUuid(null));
                dispatch(userActions.setOrderConfirmationAccess(true));
                dispatch(userActions.setOrderUrlHash(createOrderResult.data.CreateOrder.order.urlHash));
                dispatch(userActions.setLastOrderUuid(createOrderResult.data.CreateOrder.order.uuid));
                dispatch(userActions.setLastOrderPaymentType(createOrderResult.data.CreateOrder.order.payment.type));
                router.push(orderConfirmationUrl);
                return;
            }
            setOrderCreating(false);

            if (
                createOrderResult.data !== undefined &&
                createOrderResult.data.CreateOrder.orderCreated === false &&
                createOrderResult.data.CreateOrder.cart !== null
            ) {
                handleCartModifications(createOrderResult.data.CreateOrder.cart.modifications, t, changePaymentInCart);
            }

            handleFormErrors(createOrderResult.error, formProviderMethods, 'shipping data', t, formMeta.messages.error);
        },
        [
            dispatch,
            currentCart.pickupPlace,
            currentCart.cart,
            currentCart.transport,
            currentCart.payment,
            currentCart.promoCode,
            createOrder,
            cartUuid,
            formProviderMethods,
            t,
            formMeta.messages.error,
            domainUrl,
            router,
            orderConfirmationUrl,
            changePaymentInCart,
        ],
    );

    return (
        <>
            <MetaRobots content="noindex" />
            <EmptyCartWrapper currentCart={currentCart} title={t('Order')} enableHandling={!orderCreating}>
                <OrderLayout activeStep={3}>
                    <FormProvider {...formProviderMethods}>
                        <Form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
                            <ContactInformationContent />
                            <OrderAction
                                buttonBack={t('Back')}
                                buttonNext={t('Submit order')}
                                hasDisabledLook={!formProviderMethods.formState.isValid}
                                withGapTop={false}
                                withGapBottom
                                buttonBackLink={transportAndPaymentUrl}
                                isLoading={fetching}
                            />
                        </Form>
                    </FormProvider>
                </OrderLayout>
                <Webline type={'dark'}>
                    <Footer simpleFooter />
                </Webline>
                <ErrorPopup
                    isVisible={isErrorPopupVisible}
                    onCloseCallback={() => setErrorPopupVisibility(false)}
                    fields={formMeta.fields}
                    origin="shipping data"
                />
            </EmptyCartWrapper>
        </>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => initServerSideProps({ context, store, redisClient }),
        store,
    ),
);

export default ContactInformationPage;
