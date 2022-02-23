import { FC, useEffect } from 'react';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi, NotificationBarsDocumentApi } from 'graphql/generated';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import OrderConfirmation from 'components/Pages/OrderConfirmation';
import Registration from 'components/Pages/OrderConfirmation/Registration';
import router from 'next/router';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';

const Index: FC<ServerSidePropsType> = () => {
    const { canAccessOrderConfirmation } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl] = useGetInternationalizedStaticUrls(['/cart'], domainUrl);
    useEffect(() => {
        if (!canAccessOrderConfirmation) {
            router.replace(cartUrl);
        }
    }, []);

    if (canAccessOrderConfirmation) {
        return (
            <CommonLayout>
                <OrderConfirmation />
                <Registration />
            </CommonLayout>
        );
    }

    return null;
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
    ]);
});

export default Index;
