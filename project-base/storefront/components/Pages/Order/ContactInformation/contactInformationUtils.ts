import { ContactInformationFormMetaType } from './contactInformationFormMeta';
import {
    getDeliveryInfoFromFormValues,
    getDeliveryInfoFromSavedAndSelectedDeliveryAddress,
    getDeliveryInfoFromSelectedPickupPlace,
    getEmptyDeliveryInfo,
    getFormValuesWithoutDeliveryInfo,
    getSelectedDeliveryAddressForLoggedInUser,
} from './deliveryAddressUtils';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { handleCartModifications } from 'connectors/cart/Cart';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import {
    TypeCreateOrderMutation,
    TypeCreateOrderMutationVariables,
    useCreateOrderMutation,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { Maybe } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getGtmCreateOrderEventOrderPart, getGtmCreateOrderEventUserPart } from 'gtm/factories/getGtmCreateOrderEvent';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import { getGtmReviewConsents } from 'gtm/utils/getGtmReviewConsents';
import { saveGtmCreateOrderEventInLocalStorage } from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { Translate } from 'next-translate';
import useTranslation from 'next-translate/useTranslation';
import { NextRouter, useRouter } from 'next/router';
import { OrderConfirmationUrlQuery } from 'pages/order-confirmation';
import { SubmitHandler, UseFormReturn, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { PageLoadingStateSlice } from 'store/slices/createPageLoadingStateSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCartType } from 'types/cart';
import { CurrentCustomerType } from 'types/customer';
import { OperationResult } from 'urql';
import { ChangePaymentInCart, useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';

export const useContactInformationPageNavigation = () => {
    const { url } = useDomainConfig();
    const router = useRouter();
    const [transportAndPaymentUrl] = getInternationalizedStaticUrls(['/order/transport-and-payment'], url);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const goToPreviousStepFromContactInformationPage = () => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'transport-and-payment' });
        router.push(transportAndPaymentUrl);
    };

    return { goToPreviousStepFromContactInformationPage };
};

export const useShouldDisplayContactInformationForm = (
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
) => {
    const emailValue = useWatch({ name: formMeta.fields.email.name, control: formProviderMethods.control });
    const isEmailFilledCorrectly = !!emailValue && !formProviderMethods.formState.errors.email;

    return isEmailFilledCorrectly;
};

export const useCreateOrder = (
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
) => {
    const { t } = useTranslation();
    const [{ fetching: isCreatingOrder }, createOrderMutation] = useCreateOrderMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const currentCart = useCurrentCart(false);
    const user = useCurrentCustomerData();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();

    const { changePaymentInCart } = useChangePaymentInCart();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);
    const router = useRouter();

    const createOrder: SubmitHandler<ContactInformation> = async (formValues) => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'order-confirmation' });

        const createOrderResult = await createOrderMutation(
            getCreateOrderMutationVariables(cartUuid, formValues, currentCart.pickupPlace, user),
        );

        handleCreateOrderResult(
            cartUuid,
            formProviderMethods,
            formMeta,
            currentCart,
            createOrderResult,
            formValues,
            user,
            router,
            domainConfig,
            changePaymentInCart,
            t,
            userContactInformation,
            updateCartUuid,
            resetContactInformation,
            updatePageLoadingState,
        );
    };

    return { createOrder, isCreatingOrder };
};

const getCreateOrderMutationVariables = (
    cartUuid: string | null,
    formValues: ContactInformation,
    selectedPickupPlace: TypeListedStoreFragment | null,
    user: CurrentCustomerType | undefined | null,
) => {
    const country = formValues.country.value;
    let deliveryCountry = formValues.isDeliveryAddressDifferentFromBilling ? formValues.deliveryCountry.value : null;

    const formValuesWithoutDeliveryInfo = getFormValuesWithoutDeliveryInfo(formValues);
    let deliveryInfo = getEmptyDeliveryInfo();

    if (formValues.isDeliveryAddressDifferentFromBilling) {
        deliveryInfo = getDeliveryInfoFromFormValues(formValues);
        const savedAndSelectedDeliveryAddress = getSelectedDeliveryAddressForLoggedInUser(user, formValues);
        const savedAndSelectedDeliveryAddressUuid = savedAndSelectedDeliveryAddress?.uuid ?? null;

        if (selectedPickupPlace) {
            deliveryInfo = getDeliveryInfoFromSelectedPickupPlace(formValues, selectedPickupPlace);
            deliveryCountry = selectedPickupPlace.country.code;
        } else if (savedAndSelectedDeliveryAddressUuid) {
            deliveryInfo = getDeliveryInfoFromSavedAndSelectedDeliveryAddress(savedAndSelectedDeliveryAddressUuid);
            deliveryCountry = null;
        }
    }

    return {
        ...formValuesWithoutDeliveryInfo,
        ...deliveryInfo,
        cartUuid,
        onCompanyBehalf: formValues.customer === 'companyCustomer',
        heurekaAgreement: !formValues.isWithoutHeurekaAgreement,
        country,
        deliveryCountry,
    };
};

const handleCreateOrderResult = (
    cartUuid: string | null,
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
    currentCart: CurrentCartType,
    createOrderResult: OperationResult<TypeCreateOrderMutation, TypeCreateOrderMutationVariables>,
    formValues: ContactInformation,
    user: CurrentCustomerType | null | undefined,
    router: NextRouter,
    domainConfig: DomainConfigType,
    changePaymentInCart: ChangePaymentInCart,
    t: Translate,
    userContactInformation: ContactInformation,
    updateCartUuid: (value: string | null) => void,
    resetContactInformation: () => void,
    updatePageLoadingState: (value: Partial<PageLoadingStateSlice>) => void,
) => {
    const wasOrderCreated = createOrderResult.data?.CreateOrder.orderCreated;
    const createdOrder = createOrderResult.data?.CreateOrder.order;
    const modifiedCartAfterUnsuccessfulOrderCreation = createOrderResult.data?.CreateOrder.cart;

    if (wasOrderCreated && createdOrder) {
        const orderConfirmationUrlQuery: OrderConfirmationUrlQuery = {
            orderUuid: createdOrder.uuid,
            orderEmail: formValues.email,
            orderPaymentType: createdOrder.payment.type,
        };

        if (!user) {
            orderConfirmationUrlQuery.registrationData = JSON.stringify(formValues);
        }

        const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainConfig.url);

        router
            .replace(
                {
                    pathname: orderConfirmationUrl,
                    query: orderConfirmationUrlQuery,
                },
                orderConfirmationUrl,
            )
            .then(() => {
                handleEventsAfterOrderCreation(
                    cartUuid,
                    currentCart.cart,
                    currentCart.payment,
                    currentCart.promoCode,
                    createdOrder.number,
                    user,
                    domainConfig,
                    userContactInformation,
                    updateCartUuid,
                    resetContactInformation,
                );
            });

        return;
    }

    updatePageLoadingState({ isPageLoading: false });
    if (!wasOrderCreated && modifiedCartAfterUnsuccessfulOrderCreation) {
        handleCartModifications(modifiedCartAfterUnsuccessfulOrderCreation.modifications, t, changePaymentInCart);
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

const handleEventsAfterOrderCreation = (
    cartUuid: string | null,
    cart: Maybe<TypeCartFragment> | undefined,
    payment: Maybe<TypeSimplePaymentFragment> | undefined,
    promoCode: string | null,
    orderNumber: string,
    user: CurrentCustomerType | undefined | null,
    domainConfig: DomainConfigType,
    userContactInformation: ContactInformation,
    updateCartUuid: (value: string | null) => void,
    resetContactInformation: () => void,
) => {
    if (cart && payment && promoCode) {
        const gtmCreateOrderEventOrderPart = getGtmCreateOrderEventOrderPart(
            cart,
            payment,
            promoCode,
            orderNumber,
            getGtmReviewConsents(),
            domainConfig,
        );
        const gtmCreateOrderEventUserPart = getGtmCreateOrderEventUserPart(user, userContactInformation);

        const isPaymentWithPaymentGate = getIsPaymentWithPaymentGate(payment.type);
        if (isPaymentWithPaymentGate) {
            saveGtmCreateOrderEventInLocalStorage(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart);
        }

        const isPaymentSuccessful = isPaymentWithPaymentGate ? undefined : true;

        onGtmCreateOrderEventHandler(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart, isPaymentSuccessful);
    }

    if (cartUuid) {
        updateCartUuid(null);
    }

    resetContactInformation();
};
