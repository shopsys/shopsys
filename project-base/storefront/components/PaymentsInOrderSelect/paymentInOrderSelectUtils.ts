import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useChangePaymentInOrderMutation } from 'graphql/requests/orders/mutations/ChangePaymentInOrderMutation.generated';
import { onGtmPaymentTryEventHandler } from 'gtm/handlers/onGtmPaymentEventHandler';
import { useRouter } from 'next/router';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useChangePaymentInOrder = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { url } = useDomainConfig();
    const [orderByHashUrl, customerOrderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: '' }, '/customer/order-detail'],
        url,
    );

    const [{ fetching: isChangingPaymentInOrder }, changePaymentInOrder] = useChangePaymentInOrderMutation();

    const changePaymentInOrderHandler = async (
        orderUuid: string,
        paymentUuid: string,
        paymentName: string,
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

        if (!withRedirectAfterChanging) {
            return changePaymentInOrderData;
        }

        let redirectPromise: Promise<boolean>;

        if (isUserLoggedIn) {
            redirectPromise = router.push({
                pathname: customerOrderDetailUrl,
                query: { orderNumber: editedOrder.number },
            });
        } else {
            redirectPromise = router.push(orderByHashUrl + editedOrder.urlHash);
        }

        redirectPromise.then(() => {
            onGtmPaymentTryEventHandler(
                editedOrder.number,
                paymentName,
                true,
                undefined,
                editedOrder.paymentTransactionsCount,
            );
        });

        return changePaymentInOrderData;
    };

    return { changePaymentInOrderHandler, isChangePaymentInOrderFetching: isChangingPaymentInOrder };
};
