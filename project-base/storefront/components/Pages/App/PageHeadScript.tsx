import { GtmHeadScript } from 'components/Helpers/GtmHeadScript';
import Head from 'next/head';

type PageHeadScriptsProps = {
    baseDomain: string;
};

export const PageHeadScripts: FC<PageHeadScriptsProps> = () => (
    <Head>
        <GtmHeadScript />
    </Head>
);
