import { Inter, Raleway } from 'next/font/google';

// Inter carries the interface and the text: 400 text, 500 interface (menu, tabs, chips, product names), 600 small headings, actions and badges, 700 prices
const interFont = Inter({
    weight: ['400', '500', '600', '700'],
    subsets: ['latin-ext', 'latin'],
    variable: '--font-inter',
    display: 'swap',
    fallback: [
        '-apple-system',
        'BlinkMacSystemFont',
        '"Segoe UI"',
        'Roboto',
        '"Helvetica Neue"',
        'Arial',
        'system-ui',
        'sans-serif',
    ],
});

// Raleway is the brand font for headings from 18px and buttons only
const ralewayFont = Raleway({
    weight: ['600'],
    subsets: ['latin-ext', 'latin'],
    variable: '--font-raleway',
    display: 'swap',
    fallback: [
        '-apple-system',
        'BlinkMacSystemFont',
        '"Segoe UI"',
        'Roboto',
        '"Helvetica Neue"',
        'Arial',
        'system-ui',
        'sans-serif',
    ],
});

export const Fonts = () => {
    return (
        <style global jsx>{`
            /* on the root, so font-default and font-secondary also work in portals */
            :root {
                --font-inter: ${interFont.style.fontFamily};
                --font-raleway: ${ralewayFont.style.fontFamily};
            }
            html {
                font-family: var(--font-inter);
            }
        `}</style>
    );
};
