import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { nextReduxWrapper } from 'redux/main';
import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';

const Index: FC<ServerSidePropsType> = () => {
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

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Index;
