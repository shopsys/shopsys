import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SimpleLayout } from 'components/Layout/SimpleLayout/SimpleLayout';
import { EditProfileContent } from 'components/Pages/Customer/EditProfileContent';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'gtm/types/enums';
import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';

const EditProfilePage: FC = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [customerUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer', '/customer/edit-profile'],
        url,
    );
    const currentCustomerUserData = useCurrentCustomerData();
    const breadcrumbs: BreadcrumbFragmentApi[] = [
        { __typename: 'Link', name: t('Customer'), slug: customerUrl },
        { __typename: 'Link', name: t('Edit profile'), slug: customerEditProfileUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

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

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, authenticationRequired: true, redisClient, domainConfig, t }),
);

export default EditProfilePage;
