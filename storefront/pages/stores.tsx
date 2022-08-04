import StaticUrlGuard from 'components/Helpers/StaticUrlGuard';
import CommonLayout from 'components/Layout/CommonLayout';
import Stores from 'components/Pages/Stores';
import { useStores } from 'connectors/stores/Stores';
import { StoresQueryDocumentApi } from 'graphql/generated';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps, ServerSidePropsType } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const Index: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const stores = useStores();
    const breadcrumbs = useMemo(() => [{ name: t('Department stores'), slug: '' }], [t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('stores', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Stores')}>
                <Stores stores={stores} breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, false, [
        {
            query: StoresQueryDocumentApi,
        },
    ]);
});

export default Index;
