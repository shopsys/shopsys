import { StaticUrlGuard } from 'components/Helpers/StaticUrlGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { LoginContent } from 'components/Pages/Login/LoginContent';
import { CurrentCustomerUserQueryApi, CurrentCustomerUserQueryDocumentApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { createClient } from 'helpers/urql/createClient';
import { useGtmStaticPageView } from 'hooks/gtm/useGtmStaticPageView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo } from 'react';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { ssrExchange } from 'urql';

const LoginPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], domainUrl);
    const breadcrumbs = useMemo(() => [{ name: t('Login'), slug: loginUrl }], [loginUrl, t]);
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent('other', breadcrumbs);
    useGtmStaticPageView(gtmStaticPageViewEvent);

    return (
        <StaticUrlGuard domainUrl={domainUrl}>
            <CommonLayout title={t('Login')}>
                <LoginContent breadcrumbs={breadcrumbs} />
            </CommonLayout>
        </StaticUrlGuard>
    );
};

export const getServerSideProps = nextReduxWrapper.getServerSideProps((store) =>
    getServerSidePropsWithRedisClient(
        (redisClient) => async (context) => {
            const ssrCache = ssrExchange({ isClient: false });
            const client = await createClient(context, store, ssrCache, redisClient);
            const serverSideProps = await initServerSideProps({ context, store, client, ssrCache, redisClient });

            const customerQueryResult = client?.readQuery<CurrentCustomerUserQueryApi>(
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
        store,
    ),
);

export default LoginPage;
