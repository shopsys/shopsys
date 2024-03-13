import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useChangePaymentInOrderMutationApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { showErrorMessage, showSuccessMessage } from 'helpers/toasts';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

export const useChangePaymentInOrder = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const { url } = useDomainConfig();
    const [orderByHashUrl, customerOrderDetailUrl] = getInternationalizedStaticUrls(
        [{ url: '/order-detail/:urlHash', param: '' }, '/customer/order-detail'],
        url,
    );

    const [{ fetching: isChangePaymentInOrderFetching }, changePaymentInOrder] = useChangePaymentInOrderMutationApi();

    const changePaymentInOrderHandler = async (
        orderUuid: string,
        paymentUuid: string,
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

        if (isUserLoggedIn) {
            router.push({
                pathname: customerOrderDetailUrl,
                query: { orderNumber: editedOrder.number },
            });
        } else {
            router.push(`${orderByHashUrl}/${editedOrder.urlHash}`);
        }

        return changePaymentInOrderData;
    };

    return { changePaymentInOrderHandler, isChangePaymentInOrderFetching };
};
