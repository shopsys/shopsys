import { useEffect } from 'react';

const useClickClosePopup = (refs: React.RefObject<HTMLElement | null>[], onOutsideClick: () => void) => {
    useEffect(() => {
        const handleDocumentClick = (event: MouseEvent) => {
            const isClickedInsideRefs = refs.some((ref) => {
                return ref.current && ref.current.contains(event.target as Node);
            });

            if (!isClickedInsideRefs) {
                onOutsideClick();
            }
        };

        window.addEventListener('click', handleDocumentClick);

        return () => {
            window.removeEventListener('click', handleDocumentClick);
        };
    }, [refs, onOutsideClick]);
};

export default useClickClosePopup;
