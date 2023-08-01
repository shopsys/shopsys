import { Roboto } from 'next/font/google';

const robotoFont = Roboto({
    weight: ['400', '500', '700'],
    subsets: ['latin-ext', 'latin'],
    variable: '--font-dm_sans',
});

export const Fonts: FC = () => {
    return (
        <>
            <style jsx global>{`
                html {
                    font-family: ${robotoFont.style.fontFamily};
                }
            `}</style>
        </>
    );
};
