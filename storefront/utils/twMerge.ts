// eslint-disable-next-line no-restricted-imports
import { extendTailwindMerge } from 'tailwind-merge';

const twMerge = extendTailwindMerge({
    classGroups: {
        'font-size': [{ text: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'description', 'body', 'small', 'tiny', 'footer'] }],
        'border-radius': [{ rounded: ['none', 'full', 'round', '2xl', 'xl', 'lg', 'md', 'sm'] }],
        'z-index': [
            {
                z: [
                    'hidden',
                    'above',
                    'two',
                    'productSlider',
                    'stickyBar',
                    'overlay',
                    'menu',
                    'aboveMenu',
                    'consentPopup',
                    'cart',
                    'aboveOverlay',
                    'maximum',
                    'auto',
                ],
            },
        ],
    },
    theme: {
        color: [
            'primary',
            'white',
            'blue',
            'blueVeryLight',
            'black',
            'green',
            'greenLight',
            'greenVeryLight',
            'greenDark',
            'red',
            'redLight',
            'redDark',
            'orange',
            'orangeLight',
            'gray',
            'grayVeryLight',
            'grayLight',
            'grayLighter',
            'grayMidDarker',
            'grayDarker',
            'grayDark',
            'grayVeryDark',
            'border',
            'borderLight',
            'outlineFocus',
            'borderFocus',
            'inherit',
            'transparent',
            'currentColor',
        ],
    },
});

export default twMerge;
