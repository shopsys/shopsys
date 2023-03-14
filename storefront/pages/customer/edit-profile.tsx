import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { EditProfileContent } from 'components/Pages/Customer/EditProfileContent';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';

const EditProfilePage: FC = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [customerUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/edit-profile'],
        domainUrl,
    );
    const currentCustomerUserData = useCurrentCustomerData();
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Customer'), slug: customerUrl },
        { __typename: 'Link', name: t('Edit profile'), slug: customerEditProfileUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Edit profile')}>
                <SimpleLayout heading={t('Edit profile')} breadcrumb={breadcrumbs}>
                    {currentCustomerUserData !== undefined && currentCustomerUserData !== null && (
                        <EditProfileContent currentCustomerUser={currentCustomerUserData} />
                    )}
                </SimpleLayout>
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) =>
            initServerSideProps({ context, store, authenticationRequired: true, redisClient }),
        store,
    ),
);

export default EditProfilePage;
