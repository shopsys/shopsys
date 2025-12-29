import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { EditIcon } from 'components/Basic/Icon/EditIcon';
import { CustomerLayout } from 'components/Layout/CustomerLayout';
import { PageHero } from 'components/Layout/PageHero/PageHero';
import { EditProfileContent } from 'components/Pages/Customer/EditProfile/EditProfileContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { useUserProfileSectionLabel } from 'utils/user/useUserProfileSectionLabel';

const EditProfilePage: FC = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [customerEditProfileUrl] = getInternationalizedStaticUrls(['/customer/edit-profile'], url);
    const currentCustomerUserData = useCurrentCustomerData();
    const userProfileSectionLabel = useUserProfileSectionLabel();
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: userProfileSectionLabel, slug: customerEditProfileUrl },
    ];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <MetaRobots content="noindex" />

            <CustomerLayout breadcrumbs={breadcrumbs} title={userProfileSectionLabel}>
                {currentCustomerUserData !== undefined && (
                    <>
                        <PageHero
                            icon={EditIcon}
                            title={userProfileSectionLabel}
                            description={t(
                                'Edit your profile information to keep your account secure and enjoy personalized services.',
                            )}
                        />

                        <EditProfileContent currentCustomerUser={currentCustomerUserData} />
                    </>
                )}
            </CustomerLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                authenticationConfig: { authenticationRequired: true },
                redisClient,
                domainConfig,
                t,
            }),
);

export default EditProfilePage;
