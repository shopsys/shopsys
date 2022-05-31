import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import ResetPassword from 'components/Pages/ResetPassword';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { FC } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const Index: FC<ServerSidePropsType> = () => {
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other');
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout>
                <ResetPassword />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store);
});

export default Index;
