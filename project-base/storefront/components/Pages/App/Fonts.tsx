import { Inter, Raleway } from 'next/font/google';

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

const ralewayFont = Raleway({
    weight: ['400', '500', '600', '700'],
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

export { ralewayFont };

export const Fonts = () => {
    return (
        <style global jsx>{`
            html {
                font-family: ${interFont.style.fontFamily};
            }
            :root {
                --font-inter-fallback: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
                    system-ui, sans-serif;
                --font-raleway-fallback: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial,
                    system-ui, sans-serif;
            }
        `}</style>
    );
};
