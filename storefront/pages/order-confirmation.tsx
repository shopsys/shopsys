import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import CommonLayout from 'components/Layout/CommonLayout';
import { FC } from 'react';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import OrderConfirmation from 'components/Pages/OrderConfirmation';
import PageGuard from 'components/Helpers/PageGuard';
import Registration from 'components/Pages/OrderConfirmation/Registration';

const Index: FC<ServerSidePropsType> = () => {
    const { canAccessOrderConfirmation } = useShopsysSelector((state) => state.user);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], domainUrl);

    return (
        <PageGuard accessCondition={canAccessOrderConfirmation} errorRedirectUrl={cartUrl}>
            <CommonLayout>
                <OrderConfirmation />
                <Registration />
            </CommonLayout>
        </PageGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Index;
