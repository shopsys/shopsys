import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { Form } from 'components/Forms/Form/Form';
import { OrderLayout } from 'components/Layout/OrderLayout/OrderLayout';
import { CartLoading } from 'components/Pages/Cart/CartLoading';
import { ContactInformationContent } from 'components/Pages/Order/ContactInformation/ContactInformationContent';
import {
    useContactInformationForm,
    useContactInformationFormMeta,
} from 'components/Pages/Order/ContactInformation/contactInformationFormMeta';
import { handleCartModifications, useCurrentCart } from 'connectors/cart/Cart';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useCreateOrderMutationApi } from 'graphql/generated';
import {
    getGtmCreateOrderEventOrderPart,
    getGtmCreateOrderEventUserPart,
    useGtmStaticPageViewEvent,
} from 'gtm/helpers/eventFactories';
import { onGtmCreateOrderEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmReviewConsents } from 'gtm/helpers/gtm';
import { saveGtmCreateOrderEventInLocalStorage } from 'gtm/helpers/helpers';
import { useGtmContactInformationPageViewEvent } from 'gtm/hooks/useGtmContactInformationPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmMessageOriginType, GtmPageType } from 'gtm/types/enums';
import { handleFormErrors } from 'helpers/forms/handleFormErrors';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getIsPaymentWithPaymentGate } from 'helpers/mappers/payment';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useChangePaymentInCart } from 'hooks/cart/useChangePaymentInCart';
import { useErrorPopupVisibility } from 'hooks/forms/useErrorPopupVisibility';
import { dispatchBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OrderConfirmationQuery } from 'pages/order-confirmation';
import React, { useEffect, useState } from 'react';
import { FormProvider, SubmitHandler, useWatch } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { CustomerTypeEnum } from 'types/customer';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));
const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

const ContactInformationPage: FC<ServerSidePropsType> = () => {
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const router = useRouter();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);
    const customer = usePersistStore((store) => store.contactInformation.customer);
    const [transportAndPaymentUrl, orderConfirmationUrl, cartUrl] = getInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation', '/cart'],
        domainConfig.url,
    );
    const [orderCreating, setOrderCreating] = useState(false);
    const currentCart = useCurrentCart();
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();
    const [{ fetching }, createOrder] = useCreateOrderMutationApi();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const user = useCurrentCustomerData();
    const userContactInformation = useCurrentUserContactInformation();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact_information);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmContactInformationPageViewEvent(gtmStaticPageViewEvent);

    useEffect(() => {
        if (!customer) {
            if (user?.companyCustomer) {
                formProviderMethods.setValue(formMeta.fields.customer.name, CustomerTypeEnum.CompanyCustomer, {
                    shouldValidate: true,
                });
            } else {
                formProviderMethods.setValue(formMeta.fields.customer.name, CustomerTypeEnum.CommonCustomer, {
                    shouldValidate: true,
                });
            }
        }
    }, []);

    useEffect(() => {
        if (!currentCart.cart?.items.length) {
            router.replace(cartUrl);
        }
    }, [currentCart.cart]);

    useEffect(() => {
        if (!currentCart.transport || !currentCart.payment) {
            router.replace(transportAndPaymentUrl);
        }
    }, [currentCart.transport, currentCart.payment]);

    const onCreateOrderHandler: SubmitHandler<typeof defaultValues> = async (formValues) => {
        setOrderCreating(true);

        let deliveryInfo;

        if (currentCart.pickupPlace) {
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
            createOrderResult.data &&
            createOrderResult.data.CreateOrder.orderCreated &&
            createOrderResult.data.CreateOrder.order &&
            currentCart.cart &&
            currentCart.transport &&
            currentCart.payment
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

            if (!user) {
                query.registrationData = JSON.stringify(formValues);
            }

            router
                .replace(
                    {
                        pathname: orderConfirmationUrl,
                        query,
                    },
                    orderConfirmationUrl,
                )
                .then(() => {
                    updateCartUuid(null);
                    dispatchBroadcastChannel('refetchCart');
                    resetContactInformation();
                });

            onGtmCreateOrderEventHandler(
                gtmCreateOrderEventOrderPart,
                gtmCreateOrderEventUserPart,
                isPaymentSuccessful,
            );

            return;
        }

        setOrderCreating(false);

        if (
            createOrderResult.data &&
            !createOrderResult.data.CreateOrder.orderCreated &&
            createOrderResult.data.CreateOrder.cart
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

            <OrderLayout activeStep={3}>
                {!orderCreating ? (
                    <>
                        <FormProvider {...formProviderMethods}>
                            <Form onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}>
                                <ContactInformationContent setIsLoginPopupOpened={setIsLoginPopupOpened} />
                                <OrderAction
                                    withGapBottom
                                    buttonBack={t('Back')}
                                    buttonBackLink={transportAndPaymentUrl}
                                    buttonNext={t('Submit order')}
                                    hasDisabledLook={!formProviderMethods.formState.isValid}
                                    isLoading={fetching}
                                    withGapTop={false}
                                />
                            </Form>
                        </FormProvider>

                        {isErrorPopupVisible && (
                            <ErrorPopup
                                fields={formMeta.fields}
                                gtmMessageOrigin={GtmMessageOriginType.contact_information_page}
                                onCloseCallback={() => setErrorPopupVisibility(false)}
                            />
                        )}

                        {isLoginPopupOpened && (
                            <Popup onCloseCallback={() => setIsLoginPopupOpened(false)}>
                                <div className="h2 mb-3">{t('Login')}</div>
                                <Login defaultEmail={emailValue} />
                            </Popup>
                        )}
                    </>
                ) : (
                    <CartLoading />
                )}
            </OrderLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default ContactInformationPage;
