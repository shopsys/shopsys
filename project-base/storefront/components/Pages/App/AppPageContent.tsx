import { Fonts } from './Fonts';
import { Error503Content } from 'components/Pages/ErrorPage/Error503Content';
import { GtmHeadScript } from 'gtm/GtmHeadScript';
import { GtmProvider } from 'gtm/context/GtmProvider';
import { useCookiesStoreSync } from 'helpers/cookies/cookiesStoreUtils';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { isEnvironment } from 'helpers/isEnvironment';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useAuthLoader } from 'hooks/app/useAuthLoader';
import { usePageLoader } from 'hooks/app/usePageLoader';
import { usePersistStoreHydration } from 'hooks/app/useStoreHydration';
import { useReloadCart } from 'hooks/cart/useReloadCart';
import { useBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useSetInitialStoreValues } from 'hooks/useSetInitialStoreValues';
import { NextComponentType, NextPageContext } from 'next';
import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { ToastContainer } from 'react-toastify';
import { usePersistStore } from 'store/usePersistStore';

const UserConsent = dynamic(
    () => import('components/Blocks/UserConsent/UserConsent').then((component) => component.UserConsent),
    {
        ssr: false,
    },
);

const {
    publicRuntimeConfig: { showSymfonyToolbar },
} = getConfig();

const SymfonyDebugToolbar =
    isEnvironment('development') &&
    showSymfonyToolbar === '1' &&
    dynamic(
        () =>
            import('components/Basic/SymfonyDebugToolbar/SymfonyDebugToolbar').then(
                (component) => component.SymfonyDebugToolbar,
            ),
        {
            ssr: true,
        },
    );

type AppPageContentProps = {
    Component: NextComponentType<NextPageContext, any, any>;
    pageProps: ServerSidePropsType;
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps }) => {
    usePersistStoreHydration();
    useAuthLoader();
    usePageLoader();
    useReloadCart();
    useSetInitialStoreValues(pageProps);
    useCookiesStoreSync();

    const router = useRouter();
    const { url } = pageProps.domainConfig;

    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    const userConsent = usePersistStore((store) => store.userConsent);

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    return (
        <>
            <GtmHeadScript />
            <Fonts />

            <div id="portal" />

            <ToastContainer autoClose={6000} position="top-center" theme="colored" />

            <GtmProvider>
                {!userConsent && !isConsentUpdatePage && <UserConsent />}
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} />}
            </GtmProvider>

            {SymfonyDebugToolbar && <SymfonyDebugToolbar />}
        </>
    );
};
