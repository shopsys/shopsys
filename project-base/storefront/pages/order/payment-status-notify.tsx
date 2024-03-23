import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRouter } from 'next/router';
import { useEffect } from 'react';

const PaymentStatusNotifyPage: FC<ServerSidePropsType> = () => {
    const [, updatePaymentStatus] = useUpdatePaymentStatusMutation();
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

    const updatePaymentOnApi = async (orderUuid: string) => {
        await updatePaymentStatus({ orderUuid });
    };

    useEffect(() => {
        updatePaymentOnApi(orderUuidParam);
    }, []);

    return null;
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default PaymentStatusNotifyPage;
