import { TypeCategoriesByColumnFragment } from 'graphql/requests/navigation/fragments/CategoriesByColumnsFragment.generated';
import { useCallback, useLayoutEffect, useRef, useState } from 'react';
import { useNavigationItems } from './useNavigationItems';

type UseNavigationOverflowProps = {
    itemClassName?: string;
    navigation: TypeCategoriesByColumnFragment[];
};

export const useNavigationOverflow = ({ itemClassName, navigation }: UseNavigationOverflowProps) => {
    const navigationRef = useRef<HTMLUListElement>(null);
    const navigationItemRefs = useRef<Array<HTMLLIElement | null>>([]);
    const moreNavigationItemRef = useRef<HTMLLIElement>(null);
    const [isNavigationMeasured, setIsNavigationMeasured] = useState(false);
    const [measurementRevision, setMeasurementRevision] = useState(0);
    const [visibleNavigationItemsCount, setVisibleNavigationItemsCount] = useState(navigation.length);
    const measuredNavigationItems = useNavigationItems({ navigation, visibleItemsCount: visibleNavigationItemsCount });

    const resetNavigationMeasurement = useCallback(() => {
        setIsNavigationMeasured(false);
        setMeasurementRevision((currentMeasurementRevision) => currentMeasurementRevision + 1);
    }, []);

    const calculateVisibleNavigationItems = useCallback(() => {
        const navigationElement = navigationRef.current;
        const moreNavigationItemElement = moreNavigationItemRef.current;

        if (!navigationElement || !moreNavigationItemElement) {
            return;
        }

        const availableWidth = navigationElement.getBoundingClientRect().width;

        if (availableWidth === 0) {
            return;
        }

        const itemWidths = navigation.map(
            (_, index) => navigationItemRefs.current[index]?.getBoundingClientRect().width ?? 0,
        );

        if (itemWidths.some((itemWidth) => itemWidth === 0)) {
            return;
        }

        const totalNavigationWidth = itemWidths.reduce((totalWidth, itemWidth) => totalWidth + itemWidth, 0);

        if (totalNavigationWidth <= availableWidth) {
            setVisibleNavigationItemsCount(navigation.length);
            setIsNavigationMeasured(true);

            return;
        }

        const moreItemWidth = moreNavigationItemElement.getBoundingClientRect().width;
        let usedWidth = 0;
        let nextVisibleNavigationItemsCount = 0;

        for (const itemWidth of itemWidths) {
            if (usedWidth + itemWidth + moreItemWidth > availableWidth) {
                break;
            }

            usedWidth += itemWidth;
            nextVisibleNavigationItemsCount++;
        }

        setVisibleNavigationItemsCount(nextVisibleNavigationItemsCount);
        setIsNavigationMeasured(true);
    }, [navigation]);

    useLayoutEffect(() => {
        setIsNavigationMeasured(false);
    }, [itemClassName, navigation]);

    useLayoutEffect(() => {
        if (!isNavigationMeasured) {
            calculateVisibleNavigationItems();
        }
    }, [calculateVisibleNavigationItems, isNavigationMeasured, measurementRevision]);

    useLayoutEffect(() => {
        const navigationElement = navigationRef.current;

        if (!navigationElement) {
            return undefined;
        }

        const resizeObserver = new ResizeObserver(() => {
            resetNavigationMeasurement();
        });

        resizeObserver.observe(navigationElement);

        return () => resizeObserver.disconnect();
    }, [resetNavigationMeasurement]);

    useLayoutEffect(() => {
        window.addEventListener('resize', resetNavigationMeasurement);

        return () => window.removeEventListener('resize', resetNavigationMeasurement);
    }, [resetNavigationMeasurement]);

    const visibleNavigationItems = isNavigationMeasured ? measuredNavigationItems.visibleNavigationItems : navigation;
    const overflowNavigationItems = isNavigationMeasured ? measuredNavigationItems.overflowNavigationItems : [];

    return {
        hasOverflowNavigationItems: overflowNavigationItems.length > 0,
        isNavigationMeasured,
        moreNavigationItemRef,
        navigationItemRefs,
        navigationRef,
        overflowNavigationItems,
        shouldRenderMoreNavigationItem: !isNavigationMeasured || overflowNavigationItems.length > 0,
        visibleNavigationItems,
    };
};
