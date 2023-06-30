import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PaymentConfirmationContent } from 'components/Pages/Order/PaymentConfirmation/PaymentConfirmationContent';
import { OrderSentPageContentDocumentApi, useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { onGtmCreateOrderEventHandler, onGtmPaymentFailEventHandler } from 'helpers/gtm/eventHandlers';
import { getGtmCreateOrderEventFromLocalStorage } from 'helpers/gtm/helpers';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useEffect } from 'react';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const [checkPaymentStatusResult, checkPaymentStatus] = useCheckPaymentStatusMutationApi();

    const { orderIdentifier } = router.query;

    const orderUuidParam = getOrderUuid(orderIdentifier);

    useEffect(() => {
        checkPaymentStatus({ orderUuid: orderUuidParam }).then(({ data: checkPaymentStatusResultData }) => {
            const { gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart } =
                getGtmCreateOrderEventFromLocalStorage();
            if (
                checkPaymentStatusResultData?.CheckPaymentStatus === undefined ||
                !gtmCreateOrderEventOrderPart ||
                !gtmCreateOrderEventUserPart
            ) {
                return;
            }

            if (checkPaymentStatusResultData.CheckPaymentStatus) {
                onGtmCreateOrderEventHandler(gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart, true);
            } else {
                onGtmPaymentFailEventHandler(gtmCreateOrderEventOrderPart.id);
            }
        });
    }, []);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Order sent')}>
                <PaymentConfirmationContent
                    isSuccess={checkPaymentStatusResult.data?.CheckPaymentStatus === true}
                    orderUuid={orderUuidParam}
                />
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

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
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
    });
});

export default OrderPaymentConfirmationPage;
