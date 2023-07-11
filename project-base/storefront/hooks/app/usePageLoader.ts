import { useRouter } from 'next/router';
import Nprogress from 'nprogress';
import { useEffect } from 'react';

export const usePageLoader = () => {
    const router = useRouter();

    useEffect(() => {
        Nprogress.configure({ showSpinner: false, minimum: 0.2 });

        const onRouteChangeStart = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
            if (!shallow) {
                Nprogress.start();
            }
        };
        const onRouteChangeStop = (_targetUrl: string, { shallow }: { shallow: boolean }) => {
            if (!shallow) {
                Nprogress.done();
            }
        };

        router.events.on('routeChangeStart', onRouteChangeStart);
        router.events.on('routeChangeComplete', onRouteChangeStop);
        router.events.on('routeChangeError', onRouteChangeStop);

        return () => {
            router.events.off('routeChangeStart', onRouteChangeStart);
            router.events.off('routeChangeComplete', onRouteChangeStop);
            router.events.off('routeChangeError', onRouteChangeStop);
        };
    }, [router.events]);
};
