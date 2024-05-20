import { DeferredLoaders } from './DeferredLoaders';
import { Fonts, ralewayFont } from './Fonts';
import { Portal } from 'components/Basic/Portal/Portal';
import { DeferredSymfonyDebugToolbar } from 'components/Basic/SymfonyDebugToolbar/DeferredSymfonyDebugToolbar';
import { DeferredUserConsent } from 'components/Blocks/UserConsent/DeferredUserConsent';
import { DeferredGtmHeadScript } from 'gtm/DeferredGtmHeadScript';
import { GtmProvider } from 'gtm/context/GtmProvider';
import { NextComponentType, NextPageContext } from 'next';
import dynamic from 'next/dynamic';
import { ToastContainer } from 'react-toastify';
import { useCookiesStoreSync } from 'utils/cookies/cookiesStore';
import { ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const Error503Content = dynamic(
    () => import('components/Pages/ErrorPage/Error503Content').then((component) => component.Error503Content),
    {
        ssr: false,
    },
);

type AppPageContentProps = {
    Component: NextComponentType<NextPageContext, any, any>;
    pageProps: ServerSidePropsType;
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps }) => {
    useCookiesStoreSync();

    if (pageProps.isMaintenance) {
        return <Error503Content />;
    }

    return (
        <div className={ralewayFont.variable}>
            <GtmProvider>
                <Fonts />
                <DeferredLoaders />
                <DeferredGtmHeadScript />
                <ToastContainer autoClose={6000} position="top-center" theme="colored" />
                <Component {...pageProps} />
                <DeferredSymfonyDebugToolbar />
                <DeferredUserConsent url={pageProps.domainConfig.url} />
                <Portal />
            </GtmProvider>
        </div>
    );
};
