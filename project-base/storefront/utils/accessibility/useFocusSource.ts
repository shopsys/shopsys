import { useEffect } from 'react';

type FocusSource = 'keyboard' | 'pointer';

/**
 * Marks on `<html data-focus-source>` whether the customer last moved focus with the Tab key or with a pointer.
 * Text fields match `:focus-visible` after a click too, so the `focus-keyboard` variant (styles/base.css) reads
 * this attribute to show the focus fill on fields only during keyboard navigation.
 */
export const useFocusSource = () => {
    useEffect(() => {
        const setFocusSource = (focusSource: FocusSource) => {
            document.documentElement.dataset.focusSource = focusSource;
        };

        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Tab') {
                setFocusSource('keyboard');
            }
        };

        const handlePointerDown = () => setFocusSource('pointer');

        // Capture phase, so a component that stops propagation cannot hide the interaction.
        window.addEventListener('keydown', handleKeyDown, true);
        window.addEventListener('pointerdown', handlePointerDown, true);

        return () => {
            window.removeEventListener('keydown', handleKeyDown, true);
            window.removeEventListener('pointerdown', handlePointerDown, true);
        };
    }, []);
};
