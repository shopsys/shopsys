import { Fonts } from './Fonts';
import { GtmHeadScript } from 'gtm/GtmHeadScript';
import { GtmProvider } from 'gtm/context/GtmProvider';
import { useCookiesStoreSync } from 'helpers/cookies/useCookiesStoreSync';
import { isEnvironment } from 'helpers/isEnvironment';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { usePersistStoreHydration } from 'hooks/app/useStoreHydration';
import { useBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useSetInitialStoreValues } from 'hooks/useSetInitialStoreValues';
import { NextComponentType, NextPageContext } from 'next';
import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { Suspense } from 'react';
import { ToastContainer } from 'react-toastify';

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

const Error503Content = dynamic(
    () => import('components/Pages/ErrorPage/Error503Content').then((component) => component.Error503Content),
    {
        ssr: false,
    },
);

const Loaders = dynamic(() => import('components/Pages/App/Loaders').then((component) => component.Loaders), {
    ssr: false,
});

const UserConsent = dynamic(
    () => import('components/Blocks/UserConsent/UserConsent').then((component) => component.UserConsent),
    {
        ssr: false,
    },
);

type AppPageContentProps = {
    Component: NextComponentType<NextPageContext, any, any>;
    pageProps: ServerSidePropsType;
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps }) => {
    usePersistStoreHydration();
    useSetInitialStoreValues(pageProps);
    useCookiesStoreSync();
    const router = useRouter();

    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    if (pageProps.isMaintenance) {
        return <Error503Content />;
    }

    return (
        <GtmProvider>
            <Fonts />
            <Loaders />
            <GtmHeadScript />

            <ToastContainer autoClose={6000} position="top-center" theme="colored" />

            <Component {...pageProps} />

            {SymfonyDebugToolbar && <SymfonyDebugToolbar />}
            <Suspense>
                <div id="portal" />
                <UserConsent url={pageProps.domainConfig.url} />
            </Suspense>
        </GtmProvider>
    );
};
