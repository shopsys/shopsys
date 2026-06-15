import { useEffect, useState } from 'react';

export const useVisualViewportBottomOffset = () => {
    const [visualViewportBottomOffset, setVisualViewportBottomOffset] = useState(0);

    useEffect(() => {
        const visualViewport = window.visualViewport;

        if (!visualViewport) {
            return undefined;
        }

        const updateVisualViewportBottomOffset = () => {
            setVisualViewportBottomOffset(
                Math.max(0, Math.round(window.innerHeight - visualViewport.height - visualViewport.offsetTop)),
            );
        };

        updateVisualViewportBottomOffset();
        visualViewport.addEventListener('resize', updateVisualViewportBottomOffset);
        visualViewport.addEventListener('scroll', updateVisualViewportBottomOffset);

        return () => {
            visualViewport.removeEventListener('resize', updateVisualViewportBottomOffset);
            visualViewport.removeEventListener('scroll', updateVisualViewportBottomOffset);
        };
    }, []);

    return visualViewportBottomOffset;
};
