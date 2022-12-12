import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';
import { FC } from 'react';
import { nextReduxWrapper } from 'redux/main';

const PaymentStatusNotifyPage: FC<ServerSidePropsType> = () => {
    const [, checkPaymentStatus] = useCheckPaymentStatusMutationApi();
    const router = useRouter();
    const { orderIdentifier } = router.query;

    let orderUuidParam = '';
    if (orderIdentifier !== undefined) {
        if (Array.isArray(orderIdentifier)) {
            orderUuidParam = orderIdentifier[0];
        } else if (orderIdentifier.trim() !== '') {
            orderUuidParam = orderIdentifier.trim();
        }
    }

    const checkPaymentOnApi = async (orderUuid: string) => {
        await checkPaymentStatus({ orderUuid });
    };

    useEffectOnce(() => {
        checkPaymentOnApi(orderUuidParam);
    });

    return <></>;
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient((redisClient) => async (context) => {
        initDomainConfig(context, store);

        return initServerSideProps({ context, store, redisClient });
    }),
);

export default PaymentStatusNotifyPage;
