import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PaymentConfirmationContent } from 'components/Pages/Order/PaymentConfirmation/PaymentConfirmationContent';
import { OrderSentPageContentDocumentApi, useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const [checkPaymentStatusResult, checkPaymentStatus] = useCheckPaymentStatusMutationApi();

    const { orderIdentifier } = router.query;

    const orderUuidParam = getOrderUuid(orderIdentifier);

    const checkPaymentOnApi = async (orderUuid: string) => {
        await checkPaymentStatus({ orderUuid });
    };

    useEffectOnce(() => {
        checkPaymentOnApi(orderUuidParam);
    });

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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
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
                store,
                prefetchedQueries: [{ query: OrderSentPageContentDocumentApi, variables: { orderUuid } }],
                redisClient,
            });
        },
        store,
    ),
);

export default OrderPaymentConfirmationPage;
