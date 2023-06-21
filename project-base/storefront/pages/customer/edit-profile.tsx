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
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';

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

export const getServerSideProps = getServerSidePropsWithRedisClient(
    (redisClient) => async (context) => initServerSideProps({ context, authenticationRequired: true, redisClient }),
);

export default EditProfilePage;
