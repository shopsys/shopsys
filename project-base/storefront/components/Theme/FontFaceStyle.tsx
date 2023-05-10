import { memo } from 'react';

const FontFaceStyleStyle = () => (
    <style jsx global>{`
        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/dmSans400ext.woff2') format('woff2');
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/dmSans400.woff2') format('woff2');
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('/fonts/dmSans500ext.woff2') format('woff2');
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('/fonts/dmSans500.woff2') format('woff2');
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/dmSans700ext.woff2') format('woff2');
        }

        @font-face {
            font-family: 'DM Sans';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/dmSans700.woff2') format('woff2');
        }
    `}</style>
);

export const FontFaceStyle = memo(FontFaceStyleStyle, () => true);
