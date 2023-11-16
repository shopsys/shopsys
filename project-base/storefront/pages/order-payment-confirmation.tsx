import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { SkeletonPageConfirmation } from 'components/Blocks/Skeleton/SkeletonPageConfirmation';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentFail } from 'components/Pages/Order/PaymentConfirmation/PaymentFail';
import { PaymentSuccess } from 'components/Pages/Order/PaymentConfirmation/PaymentSuccess';
import { useUpdatePaymentStatus } from 'components/Pages/Order/PaymentConfirmation/helpers';
import { useSettingsQueryApi } from 'graphql/generated';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();

    const { orderIdentifier, orderPaymentStatusPageValidityHash } = useRouter().query;
    const orderUuid = getStringFromUrlQuery(orderIdentifier);
    const orderPaymentStatusPageValidityHashParam = getStringFromUrlQuery(orderPaymentStatusPageValidityHash);
    const paymentStatusData = useUpdatePaymentStatus(orderUuid, orderPaymentStatusPageValidityHashParam);

    const [{ data }] = useSettingsQueryApi();
    const maxAllowedPaymentTransactions = data?.settings?.maxAllowedPaymentTransactions ?? Number.MAX_SAFE_INTEGER;

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Order sent')}>
                <Webline>
                    {!paymentStatusData ? (
                        <SkeletonPageConfirmation />
                    ) : (
                        <>
                            {paymentStatusData.UpdatePaymentStatus.isPaid ? (
                                <PaymentSuccess orderUuid={orderUuid} />
                            ) : (
                                <PaymentFail
                                    orderPaymentType={paymentStatusData.UpdatePaymentStatus.paymentType}
                                    orderUuid={orderUuid}
                                    canPaymentBeRepeated={
                                        paymentStatusData.UpdatePaymentStatus.transactionCount <
                                        maxAllowedPaymentTransactions
                                    }
                                />
                            )}
                        </>
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
