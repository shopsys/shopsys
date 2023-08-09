import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { ServerSidePropsType } from 'helpers/misc/initServerSideProps';
import { useSetDomainConfig } from 'hooks/useDomainConfig';
import { NextComponentType, NextPageContext } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { ErrorBoundary } from 'react-error-boundary';
import { ToastContainer } from 'react-toastify';
import { usePersistStore } from 'store/usePersistStore';
import { Error500ContentWithBoundary } from '../ErrorPage/Error500Content';
import { Error503Content } from '../ErrorPage/Error503Content';
import { GtmHeadScript } from 'components/Pages/App/GtmHeadScript';
import Head from 'next/head';
import { useLoginLoader } from 'hooks/app/useLoginLoader';
import { usePageLoader } from 'hooks/app/usePageLoader';
import { useReloadCart } from 'hooks/cart/useReloadCart';
import { Fonts } from './Fonts';

const UserConsentContainer = dynamic(
    () =>
        import('components/Blocks/UserConsent/UserConsentContainer').then(
            (component) => component.UserConsentContainer,
        ),
    {
        ssr: false,
    },
);

type AppPageContentProps = {
    Component: NextComponentType<NextPageContext, any, any>;
    pageProps: ServerSidePropsType;
    err?: any;
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps, err }) => {
    const router = useRouter();
    const { url } = pageProps.domainConfig;
    const userConsent = usePersistStore((store) => store.userConsent);

    useSetDomainConfig(pageProps.domainConfig);
    useLoginLoader();
    usePageLoader();
    useReloadCart();

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    return (
        <>
            <Head>
                <GtmHeadScript />
            </Head>
            <Fonts />
            <div className="absolute left-0 top-0 z-overlay h-[1px] w-[1px]" id="portal" />
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <ErrorBoundary FallbackComponent={Error500ContentWithBoundary}>
                {!userConsent && !isConsentUpdatePage && <UserConsentContainer />}
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} err={err} />}
            </ErrorBoundary>
        </>
    );
};
