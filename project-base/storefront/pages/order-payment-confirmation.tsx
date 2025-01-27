import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { ConfirmationPageContent } from 'components/Blocks/ConfirmationPage/ConfirmationPageContent';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentStatus } from 'components/Pages/Order/PaymentConfirmation/PaymentStatus';
import {
    getPaymentSessionExpiredErrorMessage,
    useUpdatePaymentStatus,
} from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
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

    const { orderIdentifier, orderPaymentStatusPageValidityHash, orderEmail, orderUrlHash } = useRouter().query;
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

    const isFetchingData =
        !paymentStatusData || isOrderPaymentFailedContentFetching || isOrderPaymentSuccessfulContentFetching;

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout isFetchingData={isFetchingData} pageTypeOverride="order-confirmation" title={t('Order sent')}>
                <Webline>
                    <PaymentStatus
                        failedContentData={failedContentData}
                        orderUuid={orderUuid}
                        paymentStatusData={paymentStatusData}
                        successContentData={successContentData}
                    />
                    {paymentStatusData?.UpdatePaymentStatus.isPaid && successContentData && (
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
            authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiOrderFull],
        },
    });
});

export default OrderPaymentConfirmationPage;
