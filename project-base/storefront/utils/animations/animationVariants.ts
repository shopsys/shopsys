import { Variants } from 'framer-motion';
import { CSSProperties } from 'react';

export const collapseExpandAnimation: Variants = {
    open: (height: CSSProperties['height'] = 'auto') => ({
        height,
        transitionEnd: { overflow: 'visible' },
    }),
    closed: {
        height: 0,
        overflow: 'hidden',
        transitionEnd: { display: 'none' },
    },
};

export const fadeAnimation: Variants = {
    visible: {
        opacity: 100,
    },
    hidden: {
        opacity: 0,
    },
};
