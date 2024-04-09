import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import { useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const usePageLoader = () => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onRouteChangeStart = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
        updatePortalContent(null);
        updatePageLoadingState({ hadClientSideNavigation: true });
        if (!shallow) {
            Nprogress.start();
        }
    };
    const onRouteChangeStop = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
        updatePageLoadingState({ isPageLoading: false });

        if (!shallow) {
            Nprogress.done();
        }
    };

    useEffect(() => {
        Nprogress.configure({ showSpinner: false, minimum: 0.2 });

        router.events.on('routeChangeStart', onRouteChangeStart);
        router.events.on('routeChangeComplete', onRouteChangeStop);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
            router.events.off('routeChangeComplete', onRouteChangeStop);
        };
    }, [router.events]);
};
