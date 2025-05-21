import { useRouter } from 'next/router';
import { RefObject, useEffect, useRef } from 'react';

type MutuallyExcludedProps = {
    scrollTargetRef: RefObject<HTMLDivElement> | null;
    scrollY: number;
};

type PickOne<T, F extends keyof T> = Pick<T, F> & { [K in keyof Omit<T, F>]?: never };

type ScrollRestorationProps<E> = (E extends keyof MutuallyExcludedProps ? PickOne<MutuallyExcludedProps, E> : never) & {
    shouldScroll: boolean;
};

export const useScrollRestoration = <E extends keyof MutuallyExcludedProps>({
    scrollTargetRef,
    scrollY,
    shouldScroll,
}: ScrollRestorationProps<E>) => {
    const router = useRouter();
    const scrollRestored = useRef(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            if (shouldScroll && !scrollRestored.current) {
                scrollRestored.current = true;

                if (window.scrollY === 0) {
                    if (scrollTargetRef?.current) {
                        scrollTargetRef.current.scrollIntoView({ behavior: 'smooth' });
                    }

                    if (scrollY && scrollY > 0) {
                        window.scrollTo({
                            top: scrollY,
                            behavior: 'smooth',
                        });
                    }
                }
            }
        }, 100); // Small delay to let Next.js restore scroll first

        const handleRouteChange = () => {
            scrollRestored.current = false;
        };

        router.events.on('routeChangeStart', handleRouteChange);

        return () => {
            clearTimeout(timer);
            router.events.off('routeChangeStart', handleRouteChange);
        };
    }, [router]);
};
