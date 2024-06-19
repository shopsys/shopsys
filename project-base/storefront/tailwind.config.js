const em = (value) => value / 16 + 'em';

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./pages/**/*.{js,ts,jsx,tsx}', './components/**/*.{js,ts,jsx,tsx}'],
    theme: {
        screens: {
            xs: em(320),
            sm: em(480),
            md: em(600),
            lg: em(769),
            vl: em(1024),
            xl: em(1240),
            xxl: em(1560),
        },
        colors: {
            primary: '#009AFF',
            primaryLight: '#406594',
            primaryDark: '#004EB6',
            secondary: '#00CDBE',
            secondaryLight: '#27E8DA',
            secondaryDark: '#00BEB0',
            secondarySlate: '#CCF5F2',
            dark: '#25283D',
            grayLight: '#FAFAFA',
            graySlate: '#A3ACBD',
            skyBlue: '#7892BC',
            whiteSnow: '#F4FAFF',
            red: '#EC5353',
            white: '#fff',
            black: '#000',
            green: '#00ecb1',
            greenLight: '#2fa588',
            orange: '#ecb200',
            orangeLight: '#ffe594',
        },
        fontFamily: {
            default: ['var(--font-inter)'],
            secondary: ['var(--font-raleway)'],
        },
        zIndex: {
            hidden: -1000,
            above: 1,
            menu: 1010,
            aboveMenu: 1020,
            overlay: 1030,
            mobileMenu: 1040,
            aboveMobileMenu: 1050,
            cart: 6000,
            aboveOverlay: 10001,
            maximum: 10100,
        },
        extend: {
            lineHeight: {
                default: 1.3,
            },
            fontSize: {
                clamp: 'clamp(16px, 4vw, 22px)',
            },
        },
        plugins: [],
    },
};
