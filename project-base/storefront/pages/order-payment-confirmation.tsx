import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaymentConfirmationContent } from 'components/Pages/Order/PaymentConfirmation/PaymentConfirmationContent';
import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { onGtmCreateOrderEventHandler, onGtmPaymentFailEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmCreateOrderEventFromLocalStorage } from 'gtm/helpers/helpers';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import Skeleton from 'react-loading-skeleton';

const MAX_ALLOWED_PAYMENT_TRANSACTIONS = 2;

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const router = useRouter();
    const wasUpdatedPaymentStatusRef = useRef(false);
    const [{ data: paymentStatusData, fetching: isPaymentStatusFetching }, updatePaymentStatusMutation] =
        useCheckPaymentStatusMutationApi();

    const { orderIdentifier } = router.query;
    const orderUuidParam = getOrderUuid(orderIdentifier);

    const updatePaymentStatus = async () => {
        const checkPaymentStatusActionResult = await updatePaymentStatusMutation({ orderUuid: orderUuidParam });

        const { gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart } = getGtmCreateOrderEventFromLocalStorage();
        if (
            !checkPaymentStatusActionResult.data?.CheckPaymentStatus ||
            !gtmCreateOrderEventOrderPart ||
            !gtmCreateOrderEventUserPart
        ) {
            return;
        }

        if (checkPaymentStatusActionResult.data.CheckPaymentStatus.isPaid) {
            onGtmCreateOrderEventHandler(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart, true);
        } else {
            onGtmPaymentFailEventHandler(gtmCreateOrderEventOrderPart.id);
        }
    };

    useEffect(() => {
        if (!wasUpdatedPaymentStatusRef.current) {
            updatePaymentStatus();
            wasUpdatedPaymentStatusRef.current = true;
        }
    }, []);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Order sent')}>
                <Webline>
                    {isPaymentStatusFetching || !paymentStatusData ? (
                        <Skeleton className="h-60" containerClassName="h-full w-full" />
                    ) : (
                        <PaymentConfirmationContent
                            isPaid={paymentStatusData.UpdatePaymentStatus.isPaid}
                            orderPaymentType={paymentStatusData.UpdatePaymentStatus.paymentType}
                            orderUuid={orderUuidParam}
                            canPaymentBeRepeated={
                                paymentStatusData.UpdatePaymentStatus.transactionCount <
                                MAX_ALLOWED_PAYMENT_TRANSACTIONS
                            }
                        />
                    )}
                </Webline>
            </CommonLayout>
        </>
    );
};

const getOrderUuid = (orderIdentifier: string[] | string | undefined) => {
    let orderUuidParam = '';
    if (orderIdentifier !== undefined) {
        if (Array.isArray(orderIdentifier)) {
            orderUuidParam = orderIdentifier[0];
        } else if (orderIdentifier.trim() !== '') {
            orderUuidParam = orderIdentifier.trim();
        }
    }

    return orderUuidParam;
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const orderUuid = getOrderUuid(context.query.orderIdentifier);

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
        prefetchedQueries: [{ query: OrderSentPageContentDocumentApi, variables: { orderUuid } }],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderPaymentConfirmationPage;
