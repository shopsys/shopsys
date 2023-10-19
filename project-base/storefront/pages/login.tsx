import { CommonLayout } from 'components/Layout/CommonLayout';
import { LoginContent } from 'components/Pages/Login/LoginContent';
import {
    BreadcrumbFragmentApi,
    CurrentCustomerUserQueryApi,
    CurrentCustomerUserQueryDocumentApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { GtmPageType } from 'gtm/types/enums';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useDomainConfig } from 'hooks/useDomainConfig';
import useTranslation from 'next-translate/useTranslation';
import { createClient } from 'urql/createClient';

const LoginPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Login'), slug: loginUrl }];
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

            const customerQueryResult = client.readQuery<CurrentCustomerUserQueryApi>(
                CurrentCustomerUserQueryDocumentApi,
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
