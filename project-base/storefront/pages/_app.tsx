import '../styles/globals.css';
import '../styles/user-text.css';
import { GtmHeadScript } from 'components/Helpers/GtmHeadScript';
import { LoadingHandler } from 'components/Layout/LoadingHandler';
import { Error500ContentWithBoundary } from 'components/Pages/ErrorPage/Error500Content';
import { Error503Content } from 'components/Pages/ErrorPage/Error503Content';
import { extend, locale } from 'dayjs';
import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { getDomainConfig } from 'helpers/domain/domain';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useReloadCart } from 'hooks/cart/useReloadCart';
import { useSetDomainConfig } from 'hooks/useDomainConfig';
import i18nConfig from 'i18n';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lightgallery.css';
import appWithI18n from 'next-translate/appWithI18n';
import { withUrqlClient } from 'next-urql';
import { AppProps as NextAppProps } from 'next/app';
import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import Head from 'next/head';
import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import 'nprogress/nprogress.css';
import { PropsWithChildren, ReactElement, useEffect } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { getUrqlExchanges } from 'urql/exchanges';

extend(LocalizedFormat);

type ErrorProps = {
    err?: any;
};

type AppProps = {
    pageProps: ServerSidePropsType;
} & Omit<NextAppProps<ErrorProps>, 'pageProps'> &
    ErrorProps;

function MyApp({ Component, pageProps, err }: AppProps): ReactElement {
    const router = useRouter();
    const { url, defaultLocale } = pageProps.domainConfig;
    const userConsentCookie = getUserConsentCookie();
    const { publicRuntimeConfig } = getConfig();

    useSetDomainConfig(pageProps.domainConfig);

    useReloadCart();

    locale(defaultLocale);

    useEffect(() => {
        Nprogress.configure({ showSpinner: false, minimum: 0.2 });

        const onRouteChangeStart = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
            if (!shallow) {
                Nprogress.start();
            }
        };
        const onRouteChangeStop = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
            if (!shallow) {
                Nprogress.done();
            }
        };

        router.events.on('routeChangeStart', onRouteChangeStart);
        router.events.on('routeChangeComplete', onRouteChangeStop);
        router.events.on('routeChangeError', onRouteChangeStop);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
            router.events.off('routeChangeComplete', onRouteChangeStop);
            router.events.off('routeChangeError', onRouteChangeStop);
        };
    }, [router.events]);

    const UserConsentContainer = dynamic<PropsWithChildren<Record<string, unknown>>>(
        () =>
            import('components/Blocks/UserConsent/UserConsentContainer').then(
                (component) => component.UserConsentContainer,
            ),
        {
            ssr: false,
        },
    );

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;
    const baseDomain = publicRuntimeConfig.cdnDomain;

    return (
        <>
            <Head>
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans400ext.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans400.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans500ext.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans500.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans700ext.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <link
                    rel="preload"
                    href={`${baseDomain}/fonts/dmSans700.woff2`}
                    as="font"
                    type="font/woff2"
                    crossOrigin=""
                />
                <GtmHeadScript />
            </Head>

            <div className="absolute left-0 top-0 z-overlay h-[1px] w-[1px]" id="portal" />

            <LoadingHandler />
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
                {userConsentCookie === null && !isConsentUpdatePage && <UserConsentContainer />}
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} err={err} />}
            </ErrorBoundary>
        </>
    );
}

/**
 * We need to define "something" on the server side, even though it is not used at all.
 * On the server side, the URL is actually defined in initUrqlClient in InitServerSideProps.
 */
const getApiUrl = () => {
    let apiUrl = 'defaultUrl';
    if (typeof window !== 'undefined') {
        apiUrl = getDomainConfig(window.location.host).publicGraphqlEndpoint;
    }
    return apiUrl;
};

export default withUrqlClient(
    (ssrExchange) => ({
        url: getApiUrl(),
        exchanges: getUrqlExchanges(ssrExchange),
        /**
         * Fetcher is not provided here as it is not needed and
         * we cannot provide it, because we would need to create
         * a Redis client
         */
    }),
    { ssr: false },
)(
    // eslint-disable-next-line
    // @ts-ignore
    appWithI18n(MyApp, { ...i18nConfig }),
);
