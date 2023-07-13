import { LoadingHandler } from 'components/Layout/LoadingHandler';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useSetDomainConfig } from 'hooks/useDomainConfig';
import { NextComponentType, NextPageContext } from 'next';
import getConfig from 'next/config';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { PropsWithChildren } from 'react';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { Error500ContentWithBoundary } from '../ErrorPage/Error500Content';
import { Error503Content } from '../ErrorPage/Error503Content';
import { PageHeadScripts } from './PageHeadScript';
import { CartReloader } from './CartReloader';
import { Fonts } from './Fonts';

type AppPageContentProps = {
    Component: NextComponentType<NextPageContext, any, any>;
    pageProps: ServerSidePropsType;
    err?: any;
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps, err }) => {
    const router = useRouter();
    const { url } = pageProps.domainConfig;
    const userConsent = usePersistStore((store) => store.userConsent);
    const { publicRuntimeConfig } = getConfig();

    useSetDomainConfig(pageProps.domainConfig);

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
            <PageHeadScripts baseDomain={baseDomain} />
            <Fonts />
            <div className="absolute left-0 top-0 z-overlay h-[1px] w-[1px]" id="portal" />
            <CartReloader />
            <LoadingHandler />
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
                {!userConsent && !isConsentUpdatePage && <UserConsentContainer />}
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} err={err} />}
            </ErrorBoundary>
        </>
    );
};
