import initialStyled, { ThemedStyledInterface } from 'styled-components';
import mediaQueries from './mediaQueries';
import tinycolor from 'tinycolor2';

/* Main theme */
export const theme = {
    /* Colors */
    color: {
        base: '#0d0d0d',
        primary: '#4c5bfd',
        primaryLight: '#a3abff',
        white: '#fff',
        whitesmoke: '#e8e8ea',
        black: '#000',
        orange: '#ecb200',
        orangeLight: '#fff0c4',
        border: '#c4c9ff',
        red: '#ec5353',
        redLight: '#f2a2a2',
        green: '#00ecb1',
        greenLight: '#81f7da',
        greenVeryLight: '#e7fce6',
        greenDark: '#22b92a',
        grey: '#555764',
        greyLight: '#a4a7c1',
        greyVeryLight: '#f5f5f6',
        greyDark: '#414353',
        greyLighter: '#e2e3eb',
        blueLight: '#f2f2ff',
        blue: '#d9d9ff',
        creamWhite: '#fefefe',
        inStock: '#01946f',
    },

    /* Fonts */
    fontSize: {
        bigger: '18px',
        default: '16px',
        small: '14px',
        extraSmall: '12px',
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
        biggest: '13px',
        big: '11px',
        medium: '4px',
        small: '2px',
    },

    zIndex: {
        hidden: -1000,
        above: 1,
        overlay: 500,
        menu: 501,
        aboveMenu: 750,
        cart: 6000,
        aboveOverlay: 10001,
        maximum: 10100,
    },

    boxShadow: {
        green: '0 0 0 4px rgba(0, 236, 177, 0.12)',
    },

    /* Buttons */
    button: {
        size: {
            default: {
                height: '48px',
                lineHeight: '27px',
                paddingVertical: '10px',
                paddingHorizontal: '32px',
                fontSize: () => theme.fontSize.default,
            },
            small: {
                height: '30px',
                lineHeight: '23px',
                paddingVertical: '3px',
                paddingHorizontal: '17px',
                fontSize: () => theme.fontSize.small,
            },
        },
        variant: {
            default: {
                color: () => theme.color.white,
                background: () => theme.color.orange,
                colorHover: () => theme.color.white,
                backgroundHover: () => tinycolor(theme.color.orange).darken(10).toString(),
            },
            primary: {
                color: () => theme.color.white,
                background: () => theme.color.primary,
                colorHover: () => theme.color.white,
                backgroundHover: () => tinycolor(theme.color.primary).darken(10).toString(),
            },
            secondary: {
                color: () => theme.color.black,
                background: () => theme.color.orangeLight,
                colorHover: () => theme.color.black,
                backgroundHover: () => theme.color.white,
            },
        },
        borderRadius: {
            default: () => theme.radius.medium,
            big: () => theme.radius.big,
        },
    },

    /* Transition */
    transition: '0.2s cubic-bezier(.8, .20, .48, 1.0)',
    transitionEffect: 'cubic-bezier(.8, .20, .48, 1.0)',

    mediaQueries: { ...mediaQueries },
} as const;

export type Theme = typeof theme;

export const styled = initialStyled as ThemedStyledInterface<Theme>;
