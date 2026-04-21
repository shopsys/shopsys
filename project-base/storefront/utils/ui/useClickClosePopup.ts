import { useEffect, useEffectEvent } from 'react';

const useClickClosePopup = (refs: React.RefObject<HTMLElement | null>[], onOutsideClick: () => void) => {
    const onOutsideClickEvent = useEffectEvent(() => {
        onOutsideClick();
    });

    useEffect(() => {
        // mousedown fires before React's synthetic onClick so the target is still
        // mounted even if the click triggers a re-render that unmounts it — prevents
        // click-outside from firing spuriously when a popup swaps its own DOM
        // (e.g. Select replacing a button with an input on open).
        const handleDocumentMouseDown = (event: MouseEvent) => {
            const isClickedInsideRefs = refs.some((ref) => {
                return ref.current?.contains(event.target as Node);
            });

            if (!isClickedInsideRefs) {
                onOutsideClickEvent();
            }
        };

        window.addEventListener('mousedown', handleDocumentMouseDown);

        return () => {
            window.removeEventListener('mousedown', handleDocumentMouseDown);
        };
    }, [refs]);
};

export default useClickClosePopup;
