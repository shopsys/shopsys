import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useChangePaymentInOrderMutation } from 'graphql/requests/orders/mutations/ChangePaymentInOrderMutation.generated';
import { TypePaymentTypeEnum } from 'graphql/types';
import { onGtmPaymentTryEventHandler } from 'gtm/handlers/onGtmPaymentEventHandler';
import { useSessionStore } from 'store/useSessionStore';
import { SkeletonEnum } from 'types/skeletons';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getLocalePrefix } from 'utils/domain/domainUtils';
import { removeGoPayPaymentSession } from 'utils/goPayPaymentSessionStorage';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getIsPaymentWithPaymentGate } from 'utils/mappers/payment';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useChangePaymentInOrder = () => {
    const { t } = useTranslation();
    const isUserLoggedIn = useIsUserLoggedIn();
    const domainConfig = useDomainConfig();
    const { url } = domainConfig;
    const [orderByHashUrl, customerOrderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: '' }, '/customer/order-detail'],
        url,
    );
    const updatePageLoadingState = useSessionStore((store) => store.updatePageLoadingState);

    const [{ fetching: isChangingPaymentInOrder }, changePaymentInOrder] = useChangePaymentInOrderMutation();

    const changePaymentInOrderHandler = async (
        orderUuid: string,
        paymentUuid: string,
        paymentName: string,
        paymentType: TypePaymentTypeEnum,
        paymentGoPayBankSwift?: string | null,
        withRedirectAfterChanging = true,
    ) => {
        const { data: changePaymentInOrderData } = await changePaymentInOrder({
            input: { orderUuid, paymentGoPayBankSwift: paymentGoPayBankSwift ?? null, paymentUuid },
        });
        const editedOrder = changePaymentInOrderData?.ChangePaymentInOrder;

        if (!editedOrder) {
            showErrorMessage(t('An error occurred while changing the payment'));

            return changePaymentInOrderData;
        }

        showSuccessMessage(t('Your payment has been successfully changed'));
        removeGoPayPaymentSession();

        if (!withRedirectAfterChanging) {
            return changePaymentInOrderData;
        }

        const isNonGatewayPayment = !getIsPaymentWithPaymentGate(paymentType);
        const paymentRetryCount =
            isNonGatewayPayment && editedOrder.paymentTransactionsCount > 0
                ? editedOrder.paymentTransactionsCount
                : Math.max(editedOrder.paymentTransactionsCount - 1, 0);
        onGtmPaymentTryEventHandler(editedOrder.number, paymentName, true, undefined, paymentRetryCount);

        // Full page load to ensure fresh order data (router.push to the same URL is a no-op in Next.js)
        const targetUrl = isUserLoggedIn
            ? `${customerOrderDetailUrl}?orderNumber=${editedOrder.number}`
            : orderByHashUrl + editedOrder.urlHash;
        const localePrefix = getLocalePrefix(domainConfig);

        updatePageLoadingState({
            isPageLoading: true,
            redirectPageType: isUserLoggedIn ? SkeletonEnum.OrderDetail : SkeletonEnum.OrderDetailPublic,
        });
        window.location.assign(`${localePrefix}${targetUrl}`);

        return changePaymentInOrderData;
    };

    return { changePaymentInOrderHandler, isChangePaymentInOrderFetching: isChangingPaymentInOrder };
};
