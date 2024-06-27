import { getCouldNotFindUserConsentPolicyArticleUrl } from 'components/Blocks/UserConsent/userConsentUtils';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { UserConsentContent } from 'components/Pages/UserConsent/UserConsentContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { createClient } from 'urql/createClient';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const UserConsentPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [userConsentUrl] = getInternationalizedStaticUrls(['/user-consent'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [
        { __typename: 'Link', name: t('User consent'), slug: userConsentUrl },
    ];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.user_consent, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('User consent update')}>
            <UserConsentContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t, ssrExchange }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            const settingsQueryResponse = await client!
                .query<TypeSettingsQuery, TypeSettingsQueryVariables>(SettingsQueryDocument, {})
                .toPromise();

            if (getCouldNotFindUserConsentPolicyArticleUrl(settingsQueryResponse)) {
                return {
                    notFound: true,
                };
            }

            return initServerSideProps({ context, redisClient, domainConfig, t });
        },
);

export default UserConsentPage;
