import { useChangePaymentInOrderMutationApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

export const useChangePaymentInOrder = (withRedirectAfterChanging = true) => {
    const { t } = useTranslation();
    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { url } = useDomainConfig();
    const [orderByHashUrl, customerOrderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: '' }, '/customer/order-detail'],
        url,
    );

    const [{ fetching: isChangePaymentInOrderFetching }, changePaymentInOrder] = useChangePaymentInOrderMutationApi();

    const changePaymentInOrderHandler = async (orderUuid: string, paymentUuid: string) => {
        const { data: changePaymentInOrderData } = await changePaymentInOrder({
            input: { orderUuid, paymentGoPayBankSwift: null, paymentUuid },
        });
        const editedOrder = changePaymentInOrderData?.ChangePaymentInOrder;

        if (!editedOrder) {
            showErrorMessage(t('An error occurred while changing the payment'));

            return;
        }

        showSuccessMessage(t('Your payment has been successfully changed'));

        if (withRedirectAfterChanging) {
            if (isUserLoggedIn) {
                router.push({
                    pathname: customerOrderDetailUrl,
                    query: { orderNumber: editedOrder.number },
                });
            } else {
                router.push(`${orderByHashUrl}/${editedOrder.urlHash}`);
            }
        }
    };

    return { changePaymentInOrderHandler, isChangePaymentInOrderFetching };
};
