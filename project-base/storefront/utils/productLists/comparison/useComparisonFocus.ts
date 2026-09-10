import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { FocusEvent, useEffect, useRef } from 'react';

export const useComparisonFocus = (visibleProducts: TypeProductInProductListFragment[]) => {
    const contentRef = useRef<HTMLElement>(null);
    const focusedCard = useRef<{ element: HTMLElement; index: number } | null>(null);

    useEffect(() => {
        const focused = focusedCard.current;
        if (focused && !focused.element.isConnected && document.activeElement === document.body) {
            const cards = contentRef.current?.querySelectorAll('[data-comparison-product]');
            const nextCard = cards?.[Math.min(focused.index, cards.length - 1)];
            nextCard?.querySelector<HTMLElement>('button, a')?.focus();
        }
    }, [visibleProducts]);

    const handleFocusCapture = (event: FocusEvent<HTMLElement>) => {
        const card = event.target.closest<HTMLElement>('[data-comparison-product]');
        if (card) {
            focusedCard.current = { element: event.target, index: Number(card.dataset.comparisonProduct) };
        }
    };

    return { contentRef, handleFocusCapture };
};
