import { useEffect, useEffectEvent } from 'react';

const useClickClosePopup = (refs: React.RefObject<HTMLElement | null>[], onOutsideClick: () => void) => {
    const onOutsideClickEvent = useEffectEvent(() => {
        onOutsideClick();
    });

    useEffect(() => {
        const handleDocumentClick = (event: MouseEvent) => {
            const isClickedInsideRefs = refs.some((ref) => {
                return ref.current && ref.current.contains(event.target as Node);
            });

            if (!isClickedInsideRefs) {
                onOutsideClickEvent();
            }
        };

        window.addEventListener('click', handleDocumentClick);

        return () => {
            window.removeEventListener('click', handleDocumentClick);
        };
    }, [refs]);
};

export default useClickClosePopup;
