import { Inter, Raleway } from 'next/font/google';

const interFont = Inter({
    weight: ['500', '700'],
    subsets: ['latin-ext', 'latin'],
    variable: '--font-inter',
});

export const ralewayFont = Raleway({
    weight: ['500', '700'],
    subsets: ['latin-ext', 'latin'],
    variable: '--font-raleway',
});

export const Fonts: FC = () => {
    return (
        <style global jsx>{`
            html {
                font-family: ${interFont.style.fontFamily};
            }
        `}</style>
    );
};
