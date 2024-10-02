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
import {
    TypeCreateOrderMutation,
    TypeCreateOrderMutationVariables,
    useCreateOrderMutation,
} from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { getGtmCreateOrderEventOrderPart, getGtmCreateOrderEventUserPart } from 'gtm/factories/getGtmCreateOrderEvent';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import { getGtmReviewConsents } from 'gtm/utils/getGtmReviewConsents';
import { saveGtmCreateOrderEventInLocalStorage } from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { OrderConfirmationUrlQuery } from 'pages/order-confirmation';
import { SubmitHandler, UseFormReturn, useWatch } from 'react-hook-form';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { CurrentCustomerType } from 'types/customer';
import { OperationResult } from 'urql';
import { useChangePaymentInCart } from 'utils/cart/useChangePaymentInCart';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { isPacketeryTransport } from 'utils/packetery';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
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
    const [{ fetching: isCreatingOrder }, createOrderMutation] = useCreateOrderMutation();
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const currentCart = useCurrentCart(false);
    const user = useCurrentCustomerData();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const handleCreateOrderResult = useHandleCreateOrderResult();

    const createOrder: SubmitHandler<ContactInformation> = async (formValues) => {
        updatePageLoadingState({ isPageLoading: true, redirectPageType: 'order-confirmation' });

        const createOrderResult = await createOrderMutation(
            getCreateOrderMutationVariables(
                cartUuid,
                { ...formValues, email: user?.email ?? formValues.email },
                currentCart.pickupPlace,
                user,
                isPacketeryTransport(currentCart.transport?.transportTypeCode),
            ),
        );

        handleCreateOrderResult(formProviderMethods, formMeta, createOrderResult, formValues);
    };

    return { createOrder, isCreatingOrder };
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
        ...deliveryInfo,
        cartUuid,
        onCompanyBehalf: formValues.customer === 'companyCustomer',
        heurekaAgreement: !formValues.isWithoutHeurekaAgreement,
        country,
        deliveryCountry,
    };
};

const useHandleCreateOrderResult = () => {
    const { t } = useTranslation();
    const user = useCurrentCustomerData();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const domainConfig = useDomainConfig();
    const { changePaymentInCart } = useChangePaymentInCart();
    const router = useRouter();
    const handleEventsAfterOrderCreation = useHandleEventsAfterOrderCreation();

    const handleCreateOrderResult = (
        formProviderMethods: UseFormReturn<ContactInformation>,
        formMeta: ContactInformationFormMetaType,
        createOrderResult: OperationResult<TypeCreateOrderMutation, TypeCreateOrderMutationVariables>,
        formValues: ContactInformation,
    ) => {
        const wasOrderCreated = createOrderResult.data?.CreateOrder.orderCreated;
        const createdOrder = createOrderResult.data?.CreateOrder.order;
        const modifiedCartAfterUnsuccessfulOrderCreation = createOrderResult.data?.CreateOrder.cart;

        if (wasOrderCreated && createdOrder) {
            const orderConfirmationUrlQuery: OrderConfirmationUrlQuery = {
                orderUuid: createdOrder.uuid,
                orderEmail: user?.email ?? formValues.email,
                orderPaymentType: createdOrder.payment.type,
                orderPaymentStatusPageValidityHash: undefined,
            };

            if (!user) {
                orderConfirmationUrlQuery.orderUrlHash = createdOrder.urlHash;
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
                    handleEventsAfterOrderCreation(createdOrder.number);
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

    return handleCreateOrderResult;
};

const useHandleEventsAfterOrderCreation = () => {
    const cartUuid = usePersistStore((store) => store.cartUuid);
    const user = useCurrentCustomerData();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const { cart, payment, promoCode } = useCurrentCart();
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const resetContactInformation = usePersistStore((store) => store.resetContactInformation);

    const handleEventsAfterOrderCreation = (orderNumber: string) => {
        if (cart && payment) {
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

            onGtmCreateOrderEventHandler(
                gtmCreateOrderEventOrderPart,
                gtmCreateOrderEventUserPart,
                !!user?.arePricesHidden,
                isPaymentSuccessful,
            );
        }

        if (cartUuid) {
            updateCartUuid(null);
        }

        resetContactInformation();
    };

    return handleEventsAfterOrderCreation;
};
