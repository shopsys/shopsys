import { Fonts } from './Fonts';
import { Error503Content } from 'components/Pages/ErrorPage/Error503Content';
import { GtmHeadScript } from 'gtm/GtmHeadScript';
import { GtmProvider } from 'gtm/context/GtmProvider';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useAuthLoader } from 'hooks/app/useAuthLoader';
import { usePageLoader } from 'hooks/app/usePageLoader';
import { useSetUserId } from 'hooks/app/useSetUserId';
import { useStoreHydration } from 'hooks/app/useStoreHydration';
import { useReloadCart } from 'hooks/cart/useReloadCart';
import { useBroadcastChannel } from 'hooks/useBroadcastChannel';
import { useSetDomainConfig } from 'hooks/useDomainConfig';
import { NextComponentType, NextPageContext } from 'next';
import dynamic from 'next/dynamic';
import { useRouter } from 'next/router';
import { ToastContainer } from 'react-toastify';
import { usePersistStore } from 'store/usePersistStore';

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
};

export const AppPageContent: FC<AppPageContentProps> = ({ Component, pageProps }) => {
    const router = useRouter();
    const { url } = pageProps.domainConfig;
    const userConsent = usePersistStore((store) => store.userConsent);

    useBroadcastChannel('reloadPage', () => {
        router.reload();
    });

    useStoreHydration();
    useSetDomainConfig(pageProps.domainConfig);
    useAuthLoader();
    usePageLoader();
    useReloadCart();
    useSetUserId();

    const [consentUpdatePageUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);
    const isConsentUpdatePage = router.asPath === consentUpdatePageUrl;

    return (
        <>
            <GtmHeadScript />
            <Fonts />
            <div className="absolute left-0 top-0 z-overlay h-[1px] w-[1px]" id="portal" />
            <ToastContainer autoClose={6000} position="top-center" theme="colored" />
            <GtmProvider>
                {!userConsent && !isConsentUpdatePage && <UserConsentContainer />}
                {pageProps.isMaintenance ? <Error503Content /> : <Component {...pageProps} />}
            </GtmProvider>
        </>
    );
};
