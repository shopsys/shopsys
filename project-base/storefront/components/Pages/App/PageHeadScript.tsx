import { GtmHeadScript } from 'components/Helpers/GtmHeadScript';
import Head from 'next/head';

type PageHeadScriptsProps = {
    baseDomain: string;
};

export const PageHeadScripts: FC<PageHeadScriptsProps> = ({ baseDomain }) => (
    <Head>
        <link
            rel="preload"
            href={`${baseDomain}/fonts/dmSans400ext.woff2`}
            as="font"
            type="font/woff2"
            crossOrigin=""
        />
        <link rel="preload" href={`${baseDomain}/fonts/dmSans400.woff2`} as="font" type="font/woff2" crossOrigin="" />
        <link
            rel="preload"
            href={`${baseDomain}/fonts/dmSans500ext.woff2`}
            as="font"
            type="font/woff2"
            crossOrigin=""
        />
        <link rel="preload" href={`${baseDomain}/fonts/dmSans500.woff2`} as="font" type="font/woff2" crossOrigin="" />
        <link
            rel="preload"
            href={`${baseDomain}/fonts/dmSans700ext.woff2`}
            as="font"
            type="font/woff2"
            crossOrigin=""
        />
        <link rel="preload" href={`${baseDomain}/fonts/dmSans700.woff2`} as="font" type="font/woff2" crossOrigin="" />
        <GtmHeadScript />
    </Head>
);
