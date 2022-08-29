import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PaymentConfirmationContent } from 'components/Pages/Order/PaymentConfirmation/PaymentConfirmationContent';
import { OrderSentPageContentDocumentApi, useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const OrderPaymentConfirmationPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
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
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Order sent')}>
                <PaymentConfirmationContent
                    isSuccess={checkPaymentStatusResult.data?.CheckPaymentStatus === true}
                    orderUuid={orderUuidParam}
                />
            </CommonLayout>
        </StaticUrlGuard>
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    const orderUuid = getOrderUuid(context.query.orderIdentifier);

    if (orderUuid === '') {
        return {
            redirect: {
                destination: '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps(context, store, false, [
        { query: OrderSentPageContentDocumentApi, variables: { orderUuid } },
    ]);
});

export default OrderPaymentConfirmationPage;
