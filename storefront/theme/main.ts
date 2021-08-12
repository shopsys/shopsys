import mediaQueries from './mediaQueries';

/* Main theme */
export const theme = {
    /* Colors */
    color: {
        base: '#0d0d0d',
        primary: '#4c5bfd',
        white: '#fff',
        black: '#000',
        orange: '#ecb200',
        orangeLight: '#fff0c4',
        baseLighter: '#a4a7c1',
        border: '#c4c9ff',
        red: '#ec5353',
        green: '#00ecb1',
        grey: '#414353',
        greyLight: '#a4a7c1',
        greyDark: '#555764',
    },

    /* Fonts */
    fontSize: {
        default: '16px',
        small: '14px',
    },

    /* Fonts family */
    fontFamily: {
        base: 'DM Sans, Arial, Helvetica, sans-serif',
    },

    /* Line heights */
    lineHeight: {
        default: 1.3,
    },

    /* Layouts */
    layout: {
        width: '1240px',
        padding: '20px',
    },

    /* Default border radius value */
    radius: {
        big: '11px',
        medium: '4px',
        small: '2px',
    },

    zIndex: {
        above: 1,
    },

    boxShadow: {
        green: '0 0 0 4px rgba(0, 236, 177, 0.12)',
    },

    /* Buttons */
    btnHeight: '48px',

    /* Transition */
    transition: '0.2s cubic-bezier(.8, .20, .48, 1.0)',

    mediaQueries: { ...mediaQueries },
} as const;

export type Theme = typeof theme;
