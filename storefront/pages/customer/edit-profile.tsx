import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { EditProfileContent } from 'components/Pages/Customer/EditProfile/EditProfileContent';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { initDomainConfig } from 'helpers/InitDomainConfig';
import { initServerSideProps } from 'helpers/InitServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { useGtmStaticPageViewEvent } from 'utils/Gtm/EventFactories';

const EditProfilePage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [customerUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/edit-profile'],
        domainUrl,
    );
    const currentCustomerUserData = useCurrentCustomerData();
    const breadcrumbs = useMemo(
        () => [
            { name: t('Customer'), slug: customerUrl },
            { name: t('Edit profile'), slug: customerEditProfileUrl },
        ],
        [customerEditProfileUrl, customerUrl, t],
    );
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Edit profile')}>
                <SimpleLayout heading={t('Edit profile')} breadcrumb={breadcrumbs}>
                    {currentCustomerUserData !== undefined && currentCustomerUserData !== null && (
                        <EditProfileContent currentCustomerUser={currentCustomerUserData} />
                    )}
                </SimpleLayout>
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) => async (context) => {
    initDomainConfig(context, store);
    return initServerSideProps(context, store, true);
});

export default EditProfilePage;
