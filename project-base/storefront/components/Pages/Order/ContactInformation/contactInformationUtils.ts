import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { hasCartModifications } from 'connectors/cart/Cart';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import {
    TypeCreateOrderMutation,
    TypeCreateOrderMutationVariables,
    useCreateOrderMutation,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getGtmCreateOrderEventOrderPart, getGtmCreateOrderEventUserPart } from 'gtm/factories/getGtmCreateOrderEvent';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import { onGtmPaymentTryEventHandler } from 'gtm/handlers/onGtmPaymentEventHandler';
import { getGtmReviewConsents } from 'gtm/utils/getGtmReviewConsents';
import { saveGtmCreateOrderEventInLocalStorage } from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { useRouter } from 'next/router';
import { SubmitHandler, UseFormReturn } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCustomerType } from 'types/customer';
import { OperationResult } from 'urql';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { saveOrderConfirmationContext } from 'utils/order/orderConfirmationContextStorage';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { isPacketeryTransport } from 'utils/transport';
import { dispatchBroadcastChannel } from 'utils/useBroadcastChannel';
import { ContactInformationFormMetaType } from './contactInformationFormMeta';
import {
    getDeliveryInfoFromFormValues,
    getDeliveryInfoFromSavedAndSelectedDeliveryAddress,
    getDeliveryInfoFromSelectedPickupPlace,
    getEmptyDeliveryInfo,
    getFormValuesWithoutDeliveryInfo,
    getSelectedDeliveryAddressForLoggedInUser,
} from './deliveryAddressUtils';

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

export const useCreateOrder = (
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
) => {
    const [, createOrderMutation] = useCreateOrderMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const currentCart = useCurrentCart(false);
    const user = useCurrentCustomerData();
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const setIsOrderRejectedByCartModifications = useSessionStore((s) => s.setIsOrderRejectedByCartModifications);
    const handleCreateOrderResult = useHandleCreateOrderResult(formProviderMethods, formMeta);

    const createOrder: SubmitHandler<ContactInformation> = async (formValues) => {
        // A second order must never be created from the same cart, not even after leaving and returning to the page
        if (useSessionStore.getState().isOrderBeingCreated) {
            return;
        }

        // The page shows a skeleton meanwhile, so the customer may leave it before the API answers
        let hasCustomerLeftPage = false;
        const markCustomerLeftPage = () => {
            hasCustomerLeftPage = true;
        };

        setIsOrderRejectedByCartModifications(false);
        updatePageLoadingState({
            isOrderBeingCreated: true,
            isPageLoading: true,
            redirectPageType: 'order-confirmation',
        });
        router.events.on('routeChangeStart', markCustomerLeftPage);

        let createOrderResult: OperationResult<TypeCreateOrderMutation, TypeCreateOrderMutationVariables>;

        try {
            createOrderResult = await createOrderMutation(
                getCreateOrderMutationVariables(
                    cartUuid,
                    { ...formValues, email: user?.email ?? formValues.email },
                    currentCart.pickupPlace,
                    user,
                    isPacketeryTransport(currentCart.transport?.transportTypeCode),
                ),
            );
        } finally {
            router.events.off('routeChangeStart', markCustomerLeftPage);
            updatePageLoadingState({ isOrderBeingCreated: false });
        }

        handleCreateOrderResult(createOrderResult, formValues, hasCustomerLeftPage);
    };

    return { createOrder };
};

const getCreateOrderMutationVariables = (
    cartUuid: string | null,
    formValues: ContactInformation,
    selectedPickupPlace: StoreOrPacketeryPoint | null,
    user: CurrentCustomerType | undefined | null,
    isPacketeryTransport: boolean,
) => {
    const country = formValues.country.value;
    let deliveryCountry = formValues.isDeliveryAddressDifferentFromBilling ? formValues.deliveryCountry.value : null;
    const telephone = {
        prefix: formValues.telephonePrefix,
        countryCode: formValues.telephonePrefixCountryCode || '',
        number: formValues.telephone,
    };

    const formValuesWithoutDeliveryInfo = getFormValuesWithoutDeliveryInfo(formValues);
    let deliveryInfo = getEmptyDeliveryInfo();

    if (formValues.isDeliveryAddressDifferentFromBilling || isPacketeryTransport) {
        deliveryInfo = getDeliveryInfoFromFormValues(formValues);
        const savedAndSelectedDeliveryAddress = getSelectedDeliveryAddressForLoggedInUser(user, formValues);
        const savedAndSelectedDeliveryAddressUuid = savedAndSelectedDeliveryAddress?.uuid ?? null;
        const packeteryPickupPointName =
            isPacketeryTransport && selectedPickupPlace?.name ? selectedPickupPlace.name : null;

        if (selectedPickupPlace) {
            deliveryInfo = getDeliveryInfoFromSelectedPickupPlace(
                formValues,
                selectedPickupPlace,
                packeteryPickupPointName,
            );
            deliveryCountry = selectedPickupPlace.country.code;
        } else if (savedAndSelectedDeliveryAddressUuid) {
            deliveryInfo = getDeliveryInfoFromSavedAndSelectedDeliveryAddress(savedAndSelectedDeliveryAddressUuid);
            deliveryCountry = null;
        }
    }

    return {
        ...formValuesWithoutDeliveryInfo,
        telephone,
        ...deliveryInfo,
        cartUuid,
        onCompanyBehalf: formValues.customer === 'companyCustomer',
        heurekaAgreement: !formValues.isWithoutHeurekaAgreement,
        country,
        deliveryCountry,
    };
};

/**
 * The alert belongs to the contact information page. When the refused order lost its items, transport or payment,
 * the checkout redirects to an earlier step and the cart modifications box there explains the change.
 */
export const getShouldShowOrderNotSubmittedAlert = (createOrderData: TypeCreateOrderMutation | undefined): boolean => {
    const modifiedCart = createOrderData?.CreateOrder.cart;

    if (!modifiedCart || createOrderData.CreateOrder.orderCreated) {
        return false;
    }

    const staysOnContactInformationPage =
        modifiedCart.items.length > 0 && !!modifiedCart.transport && !!modifiedCart.payment;

    return staysOnContactInformationPage && hasCartModifications(modifiedCart.modifications);
};

const useHandleCreateOrderResult = (
    formProviderMethods: UseFormReturn<ContactInformation>,
    formMeta: ContactInformationFormMetaType,
) => {
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const setIsOrderRejectedByCartModifications = useSessionStore((s) => s.setIsOrderRejectedByCartModifications);
    const domainConfig = useDomainConfig();
    const router = useRouter();
    const handleEventsAfterOrderCreation = useHandleEventsAfterOrderCreation();
    const handleError = useErrorHandler({
        form: formProviderMethods,
        gtmOrigin: GtmMessageOriginType.contact_information_page,
        customMessage: formMeta.messages.error,
    });

    const handleCreateOrderResult = (
        createOrderResult: OperationResult<TypeCreateOrderMutation, TypeCreateOrderMutationVariables>,
        formValues: ContactInformation,
        hasCustomerLeftPage: boolean,
    ) => {
        const wasOrderCreated = createOrderResult.data?.CreateOrder.orderCreated;
        const createdOrder = createOrderResult.data?.CreateOrder.order;

        if (wasOrderCreated && createdOrder) {
            saveOrderConfirmationContext(createdOrder.urlHash, domainConfig.url);

            const [orderConfirmationUrl] = getInternationalizedStaticUrls(['/order-confirmation'], domainConfig.url);

            router.replace(orderConfirmationUrl).then(() => {
                handleEventsAfterOrderCreation(createdOrder.number, formValues);
            });

            return;
        }

        updatePageLoadingState({ isPageLoading: false });
        // The modifications themselves reach the cart modifications box through the network response
        setIsOrderRejectedByCartModifications(
            !hasCustomerLeftPage && getShouldShowOrderNotSubmittedAlert(createOrderResult.data),
        );

        handleError(createOrderResult.error);
    };

    return handleCreateOrderResult;
};

const useHandleEventsAfterOrderCreation = () => {
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const user = useCurrentCustomerData();
    const domainConfig = useDomainConfig();
    const { cart, payment, pickupPlace, promoCodes } = useCurrentCart();
    const [{ data: settingsData }] = useSettingsQuery();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);
    const { canSeePrices } = useAuthorization();
    const { ipAddress } = useGtmContext();

    const handleEventsAfterOrderCreation = (orderNumber: string, formValues: ContactInformation) => {
        if (cart && payment) {
            const reviewConsents = settingsData?.settings?.heurekaEnabled
                ? getGtmReviewConsents(!formValues.isWithoutHeurekaAgreement)
                : undefined;
            const gtmCreateOrderEventOrderPart = getGtmCreateOrderEventOrderPart(
                cart,
                payment,
                promoCodes,
                orderNumber,
                reviewConsents,
                domainConfig,
            );
            const gtmCreateOrderEventUserPart = getGtmCreateOrderEventUserPart(
                user,
                formValues,
                pickupPlace,
                ipAddress,
            );
            const isPaymentWithPaymentGate = getIsPaymentWithPaymentGate(payment.type);
            const isPaymentSuccessful = isPaymentWithPaymentGate ? undefined : true;

            onGtmCreateOrderEventHandler(
                gtmCreateOrderEventOrderPart,
                gtmCreateOrderEventUserPart,
                !canSeePrices,
                isPaymentSuccessful,
            );

            if (isPaymentWithPaymentGate) {
                saveGtmCreateOrderEventInLocalStorage(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart);
            } else {
                onGtmPaymentTryEventHandler(orderNumber, payment.name, true);
            }
        }

        if (cartUuid) {
            updateCartUuid(null);
        }

        resetContactInformation();

        dispatchBroadcastChannel('refetchCart', domainConfig.domainId);
    };

    return handleEventsAfterOrderCreation;
};
