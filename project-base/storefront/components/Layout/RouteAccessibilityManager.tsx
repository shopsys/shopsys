import { useRouter } from 'next/router';
import { ReactNode, useEffect, useEffectEvent } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const RouteAccessibilityManager: FC<{ children: ReactNode }> = ({ children }) => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const handleRouteChangeStart = useEffectEvent((_url: string, options: { shallow: boolean }) => {
        const { shallow } = options;

        if (shallow) {
            return;
        }

        updatePageLoadingState({
            hadClientSideNavigation: true,
            isPageLoading: true,
        });
    });

    const handleRouteChangeComplete = useEffectEvent((_url: string, options: { shallow: boolean }) => {
        const { shallow } = options;

        if (shallow) {
            return;
        }

        updatePageLoadingState({
            isPageLoading: false,
        });

        window.setTimeout(() => {
            const pageStartElement =
                document.getElementById('skip-navigation') ??
                document.getElementById('site-header') ??
                document.getElementById('main-content');

            pageStartElement?.focus({ preventScroll: true });
        });
    });

    const handleRouteChangeError = useEffectEvent((_err: Error, _url: string, options: { shallow: boolean }) => {
        const { shallow } = options;

        if (shallow) {
            return;
        }

        updatePageLoadingState({
            isPageLoading: false,
        });
    });

    useEffect(() => {
        router.events.on('routeChangeStart', handleRouteChangeStart);
        router.events.on('routeChangeComplete', handleRouteChangeComplete);
        router.events.on('routeChangeError', handleRouteChangeError);

        return () => {
            router.events.off('routeChangeStart', handleRouteChangeStart);
            router.events.off('routeChangeComplete', handleRouteChangeComplete);
            router.events.off('routeChangeError', handleRouteChangeError);
        };
    }, [router.events]);

    return <>{children}</>;
};
