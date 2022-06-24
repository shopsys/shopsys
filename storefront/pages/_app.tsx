import { PortalContainer } from 'components/Basic/Portal/Portal.style';
import Error500 from 'components/Pages/ErrorPage/500';
import ShopsysGlobalProvider from 'context/ShopsysGlobalProvider';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { useReloadCart } from 'hooks/cart/UseReloadCart';
import i18nConfig from 'i18n';
import appWithI18n from 'next-translate/appWithI18n';
import { withUrqlClient } from 'next-urql';
import { AppProps } from 'next/app';
import dynamic from 'next/dynamic';
import Head from 'next/head';
import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import 'nprogress/nprogress.css';
import { PropsWithChildren, ReactElement, useCallback, useEffect } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import { nextReduxWrapper, useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';
import { getDomainConfig } from 'utils/Domain/Domain';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

type AppPropsWithError = AppProps & {
    err?: any;
};

function MyApp({ Component, pageProps, err }: AppPropsWithError): ReactElement {
    const router = useRouter();
    const dispatch = useShopsysDispatch();
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const userConsentCookie = getUserConsentCookie();
    useReloadCart();

    const handleRouteChangeStart = useCallback(
        (targetUrl: string) => {
            if (targetUrl.split(/(\?|#)/)[0] !== router.asPath.split(/(\?|#)/)[0]) {
                dispatch(optionsFilterActions.resetOptionsFilter());
            }
        },
        [router.asPath, dispatch],
    );

    useEffect(() => {
        router.events.on('routeChangeComplete', handleRouteChangeStart);

        return () => {
            router.events.off('routeChangeComplete', handleRouteChangeStart);
        };
    }, [router.events, handleRouteChangeStart]);

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

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], domainUrl);
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
            </Head>
            <ShopsysGlobalProvider>
                <PortalContainer id="portal" />
                <ToastContainer autoClose={6000} position="top-center" theme="colored" />
                <ErrorBoundary FallbackComponent={Error500}>
                    {userConsentCookie === null && !isConsentUpdatePage && <UserConsentContainer />}
                    <Component {...pageProps} err={err} />
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
            fetch: fetcher(null),
        }),
        { ssr: false },
    )(
        // eslint-disable-next-line
        // @ts-ignore
        appWithI18n(MyApp, { ...i18nConfig }),
    ),
);
