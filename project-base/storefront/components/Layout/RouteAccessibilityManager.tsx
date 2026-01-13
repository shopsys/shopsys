import { useRouter } from 'next/router';
import { useCallback, useEffect, ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';

export const RouteAccessibilityManager: FC<{ children: ReactNode }> = ({ children }) => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);

    const handleRouteChangeStart = useCallback(
        (_url: string, options: { shallow: boolean }) => {
            const { shallow } = options;

            if (shallow) {
                return;
            }

            updatePageLoadingState({
                hadClientSideNavigation: true,
                isPageLoading: true,
            });
        },
        [updatePageLoadingState],
    );

    const handleRouteChangeComplete = useCallback(
        (_url: string, options: { shallow: boolean }) => {
            const { shallow } = options;

            if (shallow) {
                return;
            }

            updatePageLoadingState({
                isPageLoading: false,
            });
        },
        [updatePageLoadingState],
    );

    const handleRouteChangeError = useCallback(
        (_err: Error, _url: string, options: { shallow: boolean }) => {
            const { shallow } = options;

            if (shallow) {
                return;
            }

            updatePageLoadingState({
                isPageLoading: false,
            });
        },
        [updatePageLoadingState],
    );

    useEffect(() => {
        router.events.on('routeChangeStart', handleRouteChangeStart);
        router.events.on('routeChangeComplete', handleRouteChangeComplete);
        router.events.on('routeChangeError', handleRouteChangeError);

        return () => {
            router.events.off('routeChangeStart', handleRouteChangeStart);
            router.events.off('routeChangeComplete', handleRouteChangeComplete);
            router.events.off('routeChangeError', handleRouteChangeError);
        };
    }, [router.events, handleRouteChangeStart, handleRouteChangeComplete, handleRouteChangeError]);

    return <>{children}</>;
};
