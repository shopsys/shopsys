import { useCallback, useEffect, useRef, useState } from 'react';

export const useComparisonTable = (productsCompareCount: number) => {
    const scrollRef = useRef<HTMLElement>(null);
    const [geometry, setGeometry] = useState({
        left: 0,
        max: 0,
        firstColumn: 0,
        productColumn: 0,
        viewportLeft: 0,
        viewportWidth: 0,
    });

    const calcMaxMarginLeft = useCallback(() => {
        const viewport = scrollRef.current;
        if (!viewport) {
            return;
        }
        const cells = viewport.querySelector('#js-table-compare-head')?.children;
        const next = {
            left: viewport.scrollLeft,
            viewportLeft: viewport.getBoundingClientRect().left + viewport.clientLeft,
            viewportWidth: viewport.clientWidth,
            max: Math.max(0, viewport.scrollWidth - viewport.clientWidth),
            firstColumn: cells?.[0]?.getBoundingClientRect().width ?? 0,
            productColumn: cells?.[1]?.getBoundingClientRect().width ?? 0,
        };
        setGeometry((previous) =>
            Object.keys(next).every((key) => next[key as keyof typeof next] === previous[key as keyof typeof next])
                ? previous
                : next,
        );
    }, []);

    useEffect(() => {
        const viewport = scrollRef.current;
        if (!viewport) {
            return;
        }
        const observer = new ResizeObserver(calcMaxMarginLeft);
        observer.observe(viewport);
        const table = viewport.querySelector('table');
        if (table) {
            observer.observe(table);
        }
        calcMaxMarginLeft();
        window.addEventListener('resize', calcMaxMarginLeft);
        return () => {
            observer.disconnect();
            window.removeEventListener('resize', calcMaxMarginLeft);
        };
    }, [productsCompareCount, calcMaxMarginLeft]);

    const slide = (direction: number) => {
        scrollRef.current?.scrollBy({
            left: direction * geometry.productColumn,
            behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
        });
    };

    const revealProduct = (index: number) => {
        const availableWidth = geometry.viewportWidth - geometry.firstColumn;
        const start = index * geometry.productColumn;
        const end = start + geometry.productColumn;
        if (start < geometry.left) {
            scrollRef.current?.scrollTo({ left: start });
        } else if (end > geometry.left + availableWidth) {
            scrollRef.current?.scrollTo({ left: end - availableWidth });
        }
    };

    return {
        revealProduct,
        scrollRef,
        viewportLeft: geometry.viewportLeft,
        viewportWidth: geometry.viewportWidth,
        isArrowLeftActive: geometry.left > 1,
        isArrowRightActive: geometry.left < geometry.max - 1,
        shouldShowArrows: geometry.max > 1,
        handleSlideLeft: () => slide(-1),
        handleSlideRight: () => slide(1),
        calcMaxMarginLeft,
        tableFirstColumnWidth: geometry.firstColumn,
        productColumnWidth: geometry.productColumn,
        tableMarginLeft: geometry.left,
    };
};
