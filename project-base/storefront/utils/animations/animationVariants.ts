import { Variants } from 'framer-motion';
import { CSSProperties } from 'react';

export const collapseExpandAnimation: Variants = {
    open: (height: CSSProperties['height'] = 'auto') => ({
        height,
        type: 'tween',
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

export const slideAnimation = {
    hiddenRight: {
        x: '25%',
        opacity: 0.75,
    },
    hiddenLeft: {
        x: '-25%',
        opacity: 0.75,
    },
    visible: {
        x: 0,
        opacity: 1,
    },
};
