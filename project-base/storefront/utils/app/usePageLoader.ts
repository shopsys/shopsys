import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import { useEffect, useEffectEvent, useRef } from 'react';
import type { PageType } from 'store/slices/createPageLoadingStateSlice';
import { useSessionStore } from 'store/useSessionStore';
import { getSkeletonTypeFromRouteUrl } from 'utils/skeleton/getSkeletonTypeFromRouteUrl';
import {
    getSkeletonTypeFromRouteState,
    updateCurrentHistoryStateSkeletonType,
} from 'utils/skeleton/routeStateSkeletonType';

export const usePageLoader = () => {
    const router = useRouter();
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);
    const pendingNavigationSkeletonType = useRef<PageType | undefined>(undefined);

    const onRouteChangeStart = useEffectEvent((targetUrl: string, { shallow }: { shallow: boolean }) => {
        const currentPageLoadingState = useSessionStore.getState();
        pendingNavigationSkeletonType.current =
            getSkeletonTypeFromRouteUrl(targetUrl) ??
            (currentPageLoadingState.isPageLoading ? currentPageLoadingState.redirectPageType : undefined);

        updatePortalContent(null);
        updatePageLoadingState({ hadClientSideNavigation: true });
        if (!shallow) {
            Nprogress.start();
        }
    });

    const onRouteChangeStop = useEffectEvent((targetUrl: string, { shallow }: { shallow: boolean }) => {
        const redirectPageType = pendingNavigationSkeletonType.current ?? getSkeletonTypeFromRouteUrl(targetUrl);

        updateCurrentHistoryStateSkeletonType(redirectPageType);
        pendingNavigationSkeletonType.current = undefined;
        updatePageLoadingState({ isPageLoading: false });

        if (!shallow) {
            Nprogress.done();
        }
    });

    const onRouteChangeError = useEffectEvent(
        (_error: Error, _targetUrl: string, { shallow }: { shallow: boolean }) => {
            pendingNavigationSkeletonType.current = undefined;

            if (!shallow) {
                Nprogress.done();
            }
        },
    );

    const onBeforePopState = useEffectEvent(
        (routeState: { as: string; url: string; options?: { shallow?: boolean } }) => {
            if (routeState.options?.shallow && getRoutePathWithoutQuery(routeState.url) === router.pathname) {
                return true;
            }

            updatePageLoadingState({
                isPageLoading: true,
                redirectPageType: getSkeletonTypeFromRouteState(routeState),
            });

            return true;
        },
    );

    useEffect(() => {
        Nprogress.configure({ showSpinner: false, minimum: 0.2 });
        updateCurrentHistoryStateSkeletonType(
            useSessionStore.getState().redirectPageType ??
                getSkeletonTypeFromRouteUrl(router.pathname) ??
                getSkeletonTypeFromRouteUrl(router.asPath),
        );
        router.beforePopState(onBeforePopState);

        router.events.on('routeChangeStart', onRouteChangeStart);
        router.events.on('routeChangeComplete', onRouteChangeStop);
        router.events.on('routeChangeError', onRouteChangeError);

        return () => {
            router.beforePopState(() => true);
            router.events.off('routeChangeStart', onRouteChangeStart);
            router.events.off('routeChangeComplete', onRouteChangeStop);
            router.events.off('routeChangeError', onRouteChangeError);
        };
    }, [router, router.events]);
};

const getRoutePathWithoutQuery = (routeUrl: string): string => routeUrl.split('?')[0];
