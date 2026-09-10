import { Variants } from 'framer-motion';
import { CSSProperties } from 'react';

export const collapseExpandAnimation: Variants = {
    open: (height: CSSProperties['height'] = 'auto') => ({
        height,
        transition: {
            type: 'tween',
        },
        transitionEnd: { overflow: 'visible' },
    }),
    closed: {
        height: 0,
        overflow: 'hidden',
        transition: {
            type: 'tween',
        },
        transitionEnd: { display: 'none' },
    },
};

export const collapseExpandAnimationWithMargin: Variants = {
    open: (height: CSSProperties['height'] = 'auto') => ({
        height,
        marginBottom: 0,
        transition: {
            type: 'tween',
        },
        transitionEnd: { overflow: 'visible' },
    }),
    closed: {
        height: 0,
        marginBottom: -20,
        overflow: 'hidden',
        transition: {
            type: 'tween',
        },
        transitionEnd: { display: 'none' },
    },
};

export const fadeAnimation: Variants = {
    visible: {
        opacity: 1,
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
