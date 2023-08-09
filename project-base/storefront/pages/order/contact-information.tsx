import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { Form } from 'components/Forms/Form/Form';
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
import {
    getGtmCreateOrderEventOrderPart,
    getGtmCreateOrderEventUserPart,
    useGtmStaticPageViewEvent,
} from 'helpers/gtm/eventFactories';
import { onGtmCreateOrderEventHandler } from 'helpers/gtm/eventHandlers';
import { getGtmReviewConsents } from 'helpers/gtm/gtm';
import { saveGtmCreateOrderEventInLocalStorage } from 'helpers/gtm/helpers';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getIsPaymentWithPaymentGate } from 'helpers/mappers/payment';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { useHandleContactInformationNonTextChanges } from 'hooks/forms/useHandleContactInformationNonTextChanges';
import { useGtmContactInformationPageViewEvent } from 'hooks/gtm/useGtmContactInformationPageViewEvent';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OrderConfirmationQuery } from 'pages/order-confirmation';
import React, { useEffect, useState } from 'react';
import { FormProvider, SubmitHandler } from 'react-hook-form';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';
import { GtmMessageOriginType, GtmPageType } from 'types/gtm/enums';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const router = useRouter();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateUserState = usePersistStore((store) => store.updateUserState);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);
    const customer = usePersistStore((store) => store.contactInformation.customer);
    const [transportAndPaymentUrl, orderConfirmationUrl] = getInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainConfig.url,
    );
    const [orderCreating, setOrderCreating] = useState(false);
    const currentCart = useCurrentCart();
    const [changePaymentInCart] = useChangePaymentInCart();
    const t = useTypedTranslationFunction();
    const [{ fetching }, createOrder] = useCreateOrderMutationApi();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const { user, isUserLoggedIn } = useCurrentUserData();
    const userContactInformation = useCurrentUserContactInformation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact_information);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmContactInformationPageViewEvent(gtmStaticPageViewEvent);

    useHandleContactInformationNonTextChanges(formProviderMethods.control, formMeta);

    useEffect(() => {
        if (customer === undefined) {
            if (isUserLoggedIn && user?.companyCustomer) {
                formProviderMethods.setValue(formMeta.fields.customer.name, CustomerTypeEnum.CompanyCustomer);
            } else {
                formProviderMethods.setValue(formMeta.fields.customer.name, CustomerTypeEnum.CommonCustomer);
            }
        }
    }, []);

    const onCreateOrderHandler: SubmitHandler<typeof defaultValues> = async (formValues) => {
        setOrderCreating(true);

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
            const gtmCreateOrderEventOrderPart = getGtmCreateOrderEventOrderPart(
                currentCart.cart,
                currentCart.payment,
                currentCart.promoCode,
                createOrderResult.data.CreateOrder.order.number,
                getGtmReviewConsents(),
                domainConfig,
            );
            const gtmCreateOrderEventUserPart = getGtmCreateOrderEventUserPart(user, userContactInformation);

            const isPaymentWithPaymentGate = getIsPaymentWithPaymentGate(currentCart.payment.type);
            if (isPaymentWithPaymentGate) {
                saveGtmCreateOrderEventInLocalStorage(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart);
            }

            const isPaymentSuccessful = isPaymentWithPaymentGate ? undefined : true;

            const query: OrderConfirmationQuery = {
                orderUuid: createOrderResult.data.CreateOrder.order.uuid,
                orderEmail: formValues.email,
                orderPaymentType: createOrderResult.data.CreateOrder.order.payment.type,
            };

            onGtmCreateOrderEventHandler(
                gtmCreateOrderEventOrderPart,
                gtmCreateOrderEventUserPart,
                isPaymentSuccessful,
            );

            updateUserState({
                cartUuid: null,
            });
            resetContactInformation();

            router.replace(
                {
                    pathname: orderConfirmationUrl,
                    query,
                },
                orderConfirmationUrl,
            );

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

        handleFormErrors(
            createOrderResult.error,
            formProviderMethods,
            t,
            formMeta.messages.error,
            undefined,
            GtmMessageOriginType.contact_information_page,
        );
    };

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
                <Webline type="dark">
                    <Footer simpleFooter />
                </Webline>
                {isErrorPopupVisible && (
                    <ErrorPopup
                        onCloseCallback={() => setErrorPopupVisibility(false)}
                        fields={formMeta.fields}
                        gtmMessageOrigin={GtmMessageOriginType.contact_information_page}
                    />
                )}
            </EmptyCartWrapper>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ContactInformationPage;
