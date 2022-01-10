import { FC, useEffect } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

import CommonLayout from 'components/Layout/CommonLayout';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { NavigationQueryDocumentApi } from 'graphql/generated';
import OrderDetail from 'components/Pages/Customer/OrderDetail';
import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';

const Index: FC = () => {
    const domainUrl = useShopsysSelector((state) => state.domain);
    const [customerOrdersUrl] = useGetInternationalizedStaticUrls(['/customer/orders'], domainUrl.url);
    const router = useRouter();
    const isUserLoggedIn = useShopsysSelector((state) => state.user.isUserLoggedIn);

    const { orderNumber } = router.query;

    let orderNumberParam = '';
    if (orderNumber !== undefined) {
        if (Array.isArray(orderNumber)) {
            orderNumberParam = orderNumber[0];
        } else if (orderNumber.trim() !== '') {
            orderNumberParam = orderNumber.trim();
        }
    }

    useEffect(() => {
        if (isUserLoggedIn === false) {
            router.push('/');
        }
        if (orderNumberParam === '') {
            router.push(customerOrdersUrl);
        }
    }, []);

    return (
        <StaticUrlGuard domainUrl={domainUrl.url}>
            <CommonLayout>
                <OrderDetail orderNumber={orderNumberParam} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, [{ query: NavigationQueryDocumentApi }]);
});

export default Index;
