import { CommonLayout } from 'components/Layout/CommonLayout';
import { LoginContent } from 'components/Pages/Login/LoginContent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import {
    TypeCurrentCustomerUserQuery,
    CurrentCustomerUserQueryDocument,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { useGtmStaticPageViewEvent } from 'gtm/factories/useGtmStaticPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { createClient } from 'urql/createClient';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const LoginPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], url);
    const breadcrumbs: TypeBreadcrumbFragment[] = [{ __typename: 'Link', name: t('Login'), slug: loginUrl }];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout breadcrumbs={breadcrumbs} title={t('Login')}>
            <LoginContent />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });
            const serverSideProps = await initServerSideProps({
                context,
                client,
                domainConfig,
                ssrExchange,
            });

            const customerQueryResult = client.readQuery<TypeCurrentCustomerUserQuery>(
                CurrentCustomerUserQueryDocument,
                {},
            );
            const isLogged =
                customerQueryResult?.data?.currentCustomerUser !== undefined &&
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                customerQueryResult?.data?.currentCustomerUser !== null;

            if (isLogged) {
                let redirectUrl = '/';
                if (typeof context.query.r === 'string') {
                    redirectUrl = context.query.r;
                }

                return {
                    redirect: {
                        statusCode: 302,
                        destination: redirectUrl,
                    },
                };
            }

            return serverSideProps;
        },
);

export default LoginPage;
