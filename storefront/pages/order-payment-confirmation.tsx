import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import PaymentFail from 'components/Pages/Order/PaymentConfirmation/PaymentFail';
import PaymentSuccess from 'components/Pages/Order/PaymentConfirmation/PaymentSuccess';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';

const Index: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const router = useRouter();
    const [checkPaymentStatusResult, checkPaymentStatus] = useCheckPaymentStatusMutationApi();

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

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                {checkPaymentStatusResult.data?.CheckPaymentStatus === true && <PaymentSuccess />}
                {checkPaymentStatusResult.data?.CheckPaymentStatus === false && <PaymentFail />}
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Index;
