import { FC, useEffect } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import { nextReduxWrapper } from 'redux/main';
import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
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

    useEffect(() => {
        checkPaymentOnApi(orderUuidParam);
    }, []);

    return <></>;
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]);
});

export default Index;
