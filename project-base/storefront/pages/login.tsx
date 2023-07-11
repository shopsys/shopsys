import { CommonLayout } from 'components/Layout/CommonLayout';
import { LoginContent } from 'components/Pages/Login/LoginContent';
import {
    BreadcrumbFragmentApi,
    CurrentCustomerUserQueryApi,
    CurrentCustomerUserQueryDocumentApi,
} from 'graphql/generated';
import getT from 'next-translate/getT';
import { useGtmStaticPageViewEvent } from 'helpers/gtm/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps, ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { createClient } from 'helpers/urql/createClient';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmPageType } from 'types/gtm/enums';
import { ssrExchange } from 'urql';

const LoginPage: FC<ServerSidePropsType> = () => {
    const t = useTypedTranslationFunction();
    const { url } = useDomainConfig();
    const [loginUrl] = getInternationalizedStaticUrls(['/login'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Login'), slug: loginUrl }];
    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.other, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <CommonLayout title={t('Login')}>
            <LoginContent breadcrumbs={breadcrumbs} />
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient, domainConfig) => async (context) => {
    const ssrCache = ssrExchange({ isClient: false });
    const t = await getT(domainConfig.defaultLocale, 'common');
    const client = createClient(t, ssrCache, domainConfig.publicGraphqlEndpoint, redisClient, context);
    const serverSideProps = await initServerSideProps({ context, client, ssrCache, redisClient });

    const customerQueryResult = client?.readQuery<CurrentCustomerUserQueryApi>(CurrentCustomerUserQueryDocumentApi, {});
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
});

export default LoginPage;
