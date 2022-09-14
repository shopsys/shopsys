import { PortalContainer } from 'components/Basic/Portal/Portal.style';
import { GtmHeadScript } from 'components/Helpers/GtmHeadScript';
import { Error500Content } from 'components/Pages/ErrorPage/500/Error500Content';
import { Error503Content } from 'components/Pages/ErrorPage/503/Error503Content';
import { ShopsysGlobalProvider } from 'context/ShopsysGlobalProvider/ShopsysGlobalProvider';
import { extend, locale } from 'dayjs';
import 'dayjs/locale/cs';
import 'dayjs/locale/sk';
import LocalizedFormat from 'dayjs/plugin/localizedFormat';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { getDomainConfig } from 'helpers/domain/domain';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useReloadCart } from 'hooks/cart/useReloadCart';
import i18nConfig from 'i18n';
import 'lightgallery/css/lg-thumbnail.css';
import 'lightgallery/css/lightgallery.css';
import appWithI18n from 'next-translate/appWithI18n';
import { withUrqlClient } from 'next-urql';
import { AppProps as NextAppProps } from 'next/app';
import dynamic from 'next/dynamic';
import Head from 'next/head';
import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import 'nprogress/nprogress.css';
import { PropsWithChildren, ReactElement, useEffect } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { nextReduxWrapper, useShopsysSelector } from 'redux/main';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

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
    const { url, defaultLocale } = useShopsysSelector((state) => state.domain);
    const userConsentCookie = getUserConsentCookie();
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
            import('components/Blocks/UserConsent/UserConsentContainer/UserConsentContainer').then(
                (component) => component.UserConsentContainer,
            ),
        {
            ssr: false,
        },
    );

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    return (
        <>
            <Head>
                <link rel="preload" href="/fonts/dmSans400ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans400.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans500ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans500.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans700ext.woff2" as="font" type="font/woff2" crossOrigin="" />
                <link rel="preload" href="/fonts/dmSans700.woff2" as="font" type="font/woff2" crossOrigin="" />
                <GtmHeadScript />
            </Head>
            <ShopsysGlobalProvider>
                <PortalContainer id="portal" />
                <ToastContainer autoClose={6000} position="top-center" theme="colored" />
                <ErrorBoundary FallbackComponent={Error500Content}>
                    {userConsentCookie === null && !isConsentUpdatePage && <UserConsentContainer />}
                    {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} err={err} />}
                </ErrorBoundary>
            </ShopsysGlobalProvider>
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

export default nextReduxWrapper.withRedux(
    withUrqlClient(
        (ssrExchange) => ({
            url: getApiUrl(),
            exchanges: getUrqlExchanges(ssrExchange),
            fetch: fetcher(),
        }),
        { ssr: false },
    )(
        // eslint-disable-next-line
        // @ts-ignore
        appWithI18n(MyApp, { ...i18nConfig }),
    ),
);
