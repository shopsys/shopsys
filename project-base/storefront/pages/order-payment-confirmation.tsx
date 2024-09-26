import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentFail } from 'components/Pages/Order/PaymentConfirmation/PaymentFail';
import { PaymentSuccess } from 'components/Pages/Order/PaymentConfirmation/PaymentSuccess';
import {
    getPaymentSessionExpiredErrorMessage,
    useUpdatePaymentStatus,
} from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { useOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { useOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const { orderIdentifier, orderPaymentStatusPageValidityHash } = useRouter().query;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const orderPaymentStatusPageValidityHashParam = getStringFromUrlQuery(orderPaymentStatusPageValidityHash);
    const paymentStatusData = useUpdatePaymentStatus(orderUuid, orderPaymentStatusPageValidityHashParam);

    const [
        { data: failedContentData, fetching: isOrderPaymentFailedContentFetching, error: isOrderPaymentFailedError },
    ] = useOrderPaymentFailedContentQuery({
        variables: { orderUuid },
        pause: !paymentStatusData || paymentStatusData.UpdatePaymentStatus.isPaid,
    });
    const [{ data: successContentData, fetching: isOrderPaymentSuccessfulContentFetching }] =
        useOrderPaymentSuccessfulContentQuery({
            variables: { orderUuid },
            pause: !paymentStatusData || !paymentStatusData.UpdatePaymentStatus.isPaid,
        });

    const paymentSessionExpiredErrorMessage = getPaymentSessionExpiredErrorMessage(isOrderPaymentFailedError, t);

    if (paymentSessionExpiredErrorMessage) {
        return (
            <CommonLayout
                pageTypeOverride="order-confirmation"
                title={t('Order sent')}
                isFetchingData={
                    !paymentStatusData || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching
                }
            >
                <Webline>
                    <ConfirmationPageContent
                        content={paymentSessionExpiredErrorMessage}
                        heading={t('Your payment session expired')}
                    />
                </Webline>
            </CommonLayout>
        );
    }

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout
                pageTypeOverride="order-confirmation"
                title={t('Order sent')}
                isFetchingData={
                    !paymentStatusData || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching
                }
            >
                <Webline>
                    {paymentStatusData?.UpdatePaymentStatus.isPaid
                        ? successContentData && (
                              <PaymentSuccess
                                  orderPaymentSuccessfulContent={successContentData.orderPaymentSuccessfulContent}
                                  orderUuid={orderUuid}
                              />
                          )
                        : paymentStatusData &&
                          failedContentData && (
                              <PaymentFail
                                  lastUsedOrderPaymentType={paymentStatusData.UpdatePaymentStatus.payment.type}
                                  orderPaymentFailedContent={failedContentData.orderPaymentFailedContent}
                                  orderUuid={orderUuid}
                                  paymentTransactionCount={
                                      paymentStatusData.UpdatePaymentStatus.paymentTransactionsCount
                                  }
                              />
                          )}
                </Webline>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const orderUuid = getStringFromUrlQuery(context.query.orderIdentifier);

    if (orderUuid === '') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderPaymentConfirmationPage;
