import { ContactInformationFormContent } from './ContactInformationFormContent';
import { ContactInformationEmail } from './FormBlocks/ContactInformationEmail';
import { ContactInformationSendOrderButton } from './FormBlocks/ContactInformationSendOrderButton';
import { useContactInformationForm, useContactInformationFormMeta } from './contactInformationFormMeta';
import { OrderAction } from 'components/Blocks/OrderAction/OrderAction';
import { OrderContentWrapper } from 'components/Blocks/OrderContentWrapper/OrderContentWrapper';
import { Login } from 'components/Blocks/Popup/Login/Login';
import { SkeletonOrderContent } from 'components/Blocks/Skeleton/SkeletonOrderContent';
import { Form } from 'components/Forms/Form/Form';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { handleCartModifications } from 'connectors/cart/Cart';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TIDs } from 'cypress/tids';
import { useCreateOrderMutation } from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { getGtmCreateOrderEventOrderPart, getGtmCreateOrderEventUserPart } from 'gtm/factories/getGtmCreateOrderEvent';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import { getGtmReviewConsents } from 'gtm/utils/getGtmReviewConsents';
import { saveGtmCreateOrderEventInLocalStorage } from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { useGtmContactInformationPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmContactInformationPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { OrderConfirmationQuery } from 'pages/order-confirmation';
import { useState } from 'react';
import { useWatch, SubmitHandler, FormProvider } from 'react-hook-form';
import { usePersistStore } from 'store/usePersistStore';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useCountriesAsSelectOptions } from 'utils/countries/useCountriesAsSelectOptions';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { useErrorPopupVisibility } from 'utils/forms/useErrorPopupVisibility';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';

const ErrorPopup = dynamic(() => import('components/Forms/Lib/ErrorPopup').then((component) => component.ErrorPopup));
const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

export const ContactInformationWrapper: FC = () => {
    const [isLoginPopupOpened, setIsLoginPopupOpened] = useState(false);
    const router = useRouter();
    const domainConfig = useDomainConfig();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);
    const [transportAndPaymentUrl, orderConfirmationUrl] = getInternationalizedStaticUrls(
        ['/order/transport-and-payment', '/order-confirmation'],
        domainConfig.url,
    );
    const [orderCreating, setOrderCreating] = useState(false);
    const currentCart = useCurrentCart(false);
    const [changePaymentInCart] = useChangePaymentInCart();
    const { t } = useTranslation();
    const [{ fetching }, createOrder] = useCreateOrderMutation();
    const [formProviderMethods, defaultValues] = useContactInformationForm();
    const formMeta = useContactInformationFormMeta(formProviderMethods);
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const [isErrorPopupVisible, setErrorPopupVisibility] = useErrorPopupVisibility(formProviderMethods);
    const user = useCurrentCustomerData();
    const userContactInformation = useCurrentUserContactInformation();
    const isEmailFilledCorrectly = !!emailValue && !formProviderMethods.formState.errors.email;
    const countriesAsSelectOptions = useCountriesAsSelectOptions();

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.contact_information);
    useGtmPageViewEvent(gtmStaticPageViewEvent);
    useGtmContactInformationPageViewEvent(gtmStaticPageViewEvent);

    const onCreateOrderHandler: SubmitHandler<typeof defaultValues> = async (formValues) => {
        setOrderCreating(true);

        let deliveryInfo = {
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
        const deliveryAddress = user?.deliveryAddresses.find(
            (address) => address.uuid === formValues.deliveryAddressUuid,
        );

        if (currentCart.pickupPlace) {
            deliveryInfo = {
                deliveryFirstName: formValues.differentDeliveryAddress
                    ? formValues.deliveryFirstName
                    : formValues.firstName,
                deliveryLastName: formValues.differentDeliveryAddress
                    ? formValues.deliveryLastName
                    : formValues.lastName,
                deliveryCompanyName: '',
                deliveryTelephone: formValues.differentDeliveryAddress
                    ? formValues.deliveryTelephone
                    : formValues.telephone,
                deliveryStreet: currentCart.pickupPlace.street,
                deliveryCity: currentCart.pickupPlace.city,
                deliveryPostcode: currentCart.pickupPlace.postcode,
                deliveryCountry: currentCart.pickupPlace.country.code,
                differentDeliveryAddress: true,
            };
        } else if (deliveryAddress) {
            const selectedCountryOption = countriesAsSelectOptions.find(
                (option) => option.label === deliveryAddress.country,
            )!;

            if (countriesAsSelectOptions.length) {
                deliveryInfo = {
                    deliveryFirstName: deliveryAddress.firstName,
                    deliveryLastName: deliveryAddress.lastName,
                    deliveryCompanyName: deliveryAddress.companyName,
                    deliveryCountry: selectedCountryOption.value,
                    deliveryTelephone: deliveryAddress.telephone,
                    deliveryStreet: deliveryAddress.street,
                    deliveryCity: deliveryAddress.city,
                    deliveryPostcode: deliveryAddress.postcode,
                    differentDeliveryAddress: true,
                };
            }
        }

        let deliveryAddressUuid = null;

        if (formValues.deliveryAddressUuid !== '' && !currentCart.pickupPlace) {
            deliveryAddressUuid = formValues.deliveryAddressUuid;
        }

        const createOrderResult = await createOrder({
            cartUuid,
            ...formValues,
            ...deliveryInfo,
            deliveryAddressUuid,
            onCompanyBehalf: formValues.customer === 'companyCustomer',
            country: formValues.country.value,
            heurekaAgreement: !formValues.isWithoutHeurekaAgreement,
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
                    if (cartUuid) {
                        updateCartUuid(null);
                    }

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

    if (orderCreating) {
        return <SkeletonOrderContent />;
    }

    return (
        <OrderContentWrapper activeStep={3}>
            <FormProvider {...formProviderMethods}>
                <Form
                    tid={TIDs.contact_information_form}
                    onSubmit={formProviderMethods.handleSubmit(onCreateOrderHandler)}
                >
                    <>
                        <ContactInformationEmail setIsLoginPopupOpened={setIsLoginPopupOpened} />
                        {isEmailFilledCorrectly && <ContactInformationFormContent />}
                        <ContactInformationSendOrderButton />
                    </>
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
                    <Login shouldOverwriteCustomerUserCart defaultEmail={emailValue} />
                </Popup>
            )}
        </OrderContentWrapper>
    );
};
