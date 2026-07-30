import { useRouter } from 'next/router';
import { type RefObject, useEffect, useEffectEvent, useRef } from 'react';
import { mobileFirstSizes } from 'utils/mediaQueries';

const FIXED_HEADER_HEIGHT_PROPERTY = '--fixed-header-height';

type MutuallyExcludedProps = {
    scrollTargetRef: RefObject<HTMLElement | null> | null;
    scrollY: number;
};

type PickOne<T, F extends keyof T> = Pick<T, F> & {
    [K in keyof Omit<T, F>]?: never;
};

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

    const onScrollRestore = useEffectEvent(() => {
        if (!shouldScroll || scrollRestored.current) {
            return;
        }

        if (window.scrollY !== 0) {
            scrollRestored.current = true;

            return;
        }

        const isDesktopFixedHeaderPending =
            scrollTargetRef?.current &&
            window.innerWidth >= mobileFirstSizes.vl &&
            document.documentElement.style.getPropertyValue(FIXED_HEADER_HEIGHT_PROPERTY) === '';

        if (isDesktopFixedHeaderPending) {
            return;
        }

        scrollRestored.current = true;

        if (scrollTargetRef?.current) {
            scrollTargetRef.current.scrollIntoView({ behavior: 'smooth' });
        }

        if (scrollY && scrollY > 0) {
            window.scrollTo({
                top: scrollY,
                behavior: 'smooth',
            });
        }
    });

    useEffect(() => {
        const timer = setTimeout(() => onScrollRestore(), 100);
        const fixedHeaderHeightObserver = new MutationObserver(() => onScrollRestore());
        fixedHeaderHeightObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['style'],
        });

        const handleRouteChange = () => {
            scrollRestored.current = false;
        };

        router.events.on('routeChangeStart', handleRouteChange);

        return () => {
            clearTimeout(timer);
            fixedHeaderHeightObserver.disconnect();
            router.events.off('routeChangeStart', handleRouteChange);
        };
    }, [shouldScroll, scrollTargetRef, scrollY, router.events]);
};
