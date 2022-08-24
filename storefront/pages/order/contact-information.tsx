import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { Form } from 'components/Forms/Form/Form';
import { ErrorPopup } from 'components/Forms/Lib/ErrorPopup/ErrorPopup';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { Footer } from 'components/Layout/Footer/Footer';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { ContactInformationContent } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import {
    useContactInformationForm,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/formMeta';
import { useCurrentCart } from 'connectors/cart/Cart';
import { CreateOrderMutationApi, useCreateOrderMutationApi } from 'graphql/generated';
import { createClient } from 'helpers/createClient';
import { handleOrderPagesRedirect } from 'helpers/HandleOrderPagesRedirect';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useHandleErrorPopupVisibility } from 'hooks/forms/UseHandleErrorPopupVisibility';
import { useHandleFormErrors } from 'hooks/forms/UseHandleFormErrors';
import { useHandleFormSuccessfulSubmit } from 'hooks/forms/UseHandleFormSuccessfulSubmit';
import { useGtmShippingDataView } from 'hooks/gtm/useGtmShippingDataView';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import React, { FC } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { contactInformationActions } from 'redux/slices/contactInformation';
import { userActions } from 'redux/slices/user';
import { ssrExchange } from 'urql';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';
import { onPurchaseOrderGtmEventHandler } from 'utils/Gtm/EventHandlers';

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const [transportAndPaymentUrl, orderConfirmationUrl] = getInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainUrl,
    );
    const { pickupPlace, transport, payment, promoCode, cart } = useCurrentCart();
    const t = useTypedTranslationFunction();
    const [createOrderResult, createOrder] = useCreateOrderMutationApi();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useHandleErrorPopupVisibility(formProviderMethods);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('shipping data');
    useGtmStaticPageView(gtmStaticPageViewEvent);
    useGtmShippingDataView(transport, pickupPlace, payment?.name, gtmStaticPageViewEvent);

    const onSuccessfullyCreatedOrderHandler = (createOrderResultData: CreateOrderMutationApi | undefined) => {
        dispatch(userActions.setCartUuid(null));
        dispatch(userActions.setOrderConfirmationAccess(true));
        dispatch(userActions.setOrderUrlHash(createOrderResultData?.CreateOrder.urlHash));
        dispatch(userActions.setLastOrderUuid(createOrderResultData?.CreateOrder.uuid ?? ''));
        dispatch(userActions.setLastOrderPaymentType(createOrderResultData?.CreateOrder.payment.type ?? ''));
        router.push(orderConfirmationUrl);
    };

    useHandleFormSuccessfulSubmit(
        createOrderResult,
        formProviderMethods,
        defaultValues,
        onSuccessfullyCreatedOrderHandler,
    );
    useHandleFormErrors(createOrderResult.error, formProviderMethods, 'shipping data', formMeta.messages.error);
    useHandleContactInformationNonTextChanges(formProviderMethods.control, formMeta);

    const onCreateOrderHandler: SubmitHandler<typeof defaultValues> = async (formValues, event) => {
        event?.preventDefault();

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

        const order = await createOrder({
            cartUuid,
            ...formValues,
            ...deliveryInfo,
            deliveryAddressUuid: formValues.deliveryAddressUuid !== '' ? formValues.deliveryAddressUuid : null,
            onCompanyBehalf: formValues.customer === 'companyCustomer',
            country: formValues.country.value,
        });

        if (order.data !== undefined && cart !== null && transport !== null && payment !== null) {
            onPurchaseOrderGtmEventHandler(
                cart,
                transport,
                pickupPlace,
                payment,
                promoCode,
                order.data.CreateOrder.number,
                domainUrl,
            );
        }
    };

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <FormProvider {...formProviderMethods}>
                <Form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
                    <OrderLayout activeStep={3} buttonNextText={t('Submit order')}>
                        <ContactInformationContent />
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
                <Footer simpleFooter />
            </Webline>
            <ErrorPopup
                isVisible={isErrorPopupVisible}
                onCloseCallback={() => setErrorPopupVisibility(false)}
                fields={formMeta.fields}
                origin="shipping data"
            />
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const ssrCache = ssrExchange({ isClient: false });
    const client = await createClient(context, store, ssrCache);
    const redirect = await handleOrderPagesRedirect(context, store, client);
    return redirect === false ? initServerSideProps(context, store, false, [], client, ssrCache) : redirect;
});

export default ContactInformationPage;
