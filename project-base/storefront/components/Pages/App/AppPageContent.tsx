import { Portal } from 'components/Basic/Portal/Portal';
import { DeferredSymfonyDebugToolbar } from 'components/Basic/SymfonyDebugToolbar/DeferredSymfonyDebugToolbar';
import { DeferredUserConsent } from 'components/Blocks/UserConsent/DeferredUserConsent';
import { DeferredGtmHeadScript } from 'gtm/DeferredGtmHeadScript';
import { NextComponentType, NextPageContext } from 'next';
import dynamic from 'next/dynamic';
import { twJoin } from 'tailwind-merge';
import { useCookiesStoreSync } from 'utils/cookies/cookiesStore';
import { ServerSidePropsType } from 'utils/serverSide/initServerSideProps';
import { DeferredLoaders } from './DeferredLoaders';
import { DeferredSecondaryLoaders } from './DeferredSecondaryLoaders';
import { DeferredTertiaryLoaders } from './DeferredTertiaryLoaders';
import { DeferredToastContainer } from './DeferredToastContainer';
import { Fonts, ralewayFont } from './Fonts';
import { PageHeadScripts } from './PageHeadScripts';

const Error503Content = dynamic(
    () => import('components/Pages/ErrorPage/Error503Content').then((component) => component.Error503Content),
    {
        ssr: false,
    },
);

const Error403Content = dynamic(
    () => import('components/Pages/ErrorPage/Error403Content').then((component) => component.Error403Content),
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

    if (pageProps.isForbidden) {
        return (
            <>
                <DeferredLoaders />
                <DeferredSecondaryLoaders />
                <DeferredTertiaryLoaders />
                <Error403Content />
            </>
        );
    }

    if (pageProps.isMaintenance) {
        return <Error503Content />;
    }

    return (
        <div className={twJoin('h-full min-h-screen', ralewayFont.variable)}>
            <PageHeadScripts />
            <Fonts />
            <DeferredLoaders />
            <DeferredSecondaryLoaders />
            <DeferredTertiaryLoaders />
            <DeferredToastContainer />
            <Component {...pageProps} />
            <DeferredSymfonyDebugToolbar />
            <DeferredUserConsent url={pageProps.domainConfig.url} />
            <Portal />
            <DeferredGtmHeadScript gtmId={pageProps.domainConfig.gtmId} />
        </div>
    );
};
