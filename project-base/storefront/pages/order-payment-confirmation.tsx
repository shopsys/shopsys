import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import { getPaymentSessionExpiredErrorMessage } from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import { useOrderDetailByHashQuery } from 'graphql/requests/orders/queries/OrderDetailByHashQuery.generated';
import { useOrderPaymentFailedContentQuery } from 'graphql/requests/orders/queries/OrderPaymentFailedContentQuery.generated';
import { useOrderPaymentSuccessfulContentQuery } from 'graphql/requests/orders/queries/OrderPaymentSuccessfulContentQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const { orderIdentifier, orderEmail, orderUrlHash } = useRouter().query;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const urlHash = getStringFromUrlQuery(orderUrlHash);
    const [{ data: orderData, fetching: isOrderFetching }] = useOrderDetailByHashQuery({
        variables: { urlHash },
        pause: !urlHash,
    });
    const order = orderData?.order;

    const [
        { data: failedContentData, fetching: isOrderPaymentFailedContentFetching, error: isOrderPaymentFailedError },
    ] = useOrderPaymentFailedContentQuery({
        variables: { orderUuid },
        pause: !order || order.isPaid,
    });
    const [
        {
            data: successContentData,
            fetching: isOrderPaymentSuccessfulContentFetching,
            error: isOrderPaymentSuccessError,
        },
    ] = useOrderPaymentSuccessfulContentQuery({
        variables: { orderUuid },
        pause: !order || !order.isPaid,
    });

    const paymentSessionExpiredErrorMessage = getPaymentSessionExpiredErrorMessage(
        t,
        isOrderPaymentFailedError,
        isOrderPaymentSuccessError,
    );

    const isFetchingData =
        isOrderFetching || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching;

    if (paymentSessionExpiredErrorMessage) {
        return (
            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <ConfirmationPageContent
                        content={paymentSessionExpiredErrorMessage}
                        heading={t('Your payment session expired')}
                        headingClassName="text-textError"
                    />
                </Webline>
            </CommonLayout>
        );
    }

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <PaymentStatus
                        failedContentData={failedContentData}
                        orderData={orderData}
                        successContentData={successContentData}
                    />
                    // TODO: add OrderConfirmationStepper & failed/success-ContentData
                    {order?.isPaid && successContentData && (
                        <RegistrationAfterOrder
                            orderEmail={orderEmail as string | undefined}
                            orderUrlHash={orderUrlHash as string | undefined}
                            orderUuid={orderUuid}
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
        authenticationConfig: {
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    });
});

export default OrderPaymentConfirmationPage;
