import Head from 'next/head';

type MetaRobotsType = {
    content:
        | 'index'
        | 'noindex'
        | 'follow'
        | 'nofollow'
        | 'index, follow'
        | 'index, nofollow'
        | 'noindex, follow'
        | 'noindex, nofollow';
};

export const MetaRobots: FC<MetaRobotsType> = ({ content }) => (
    <Head>
        <meta content={content} name="robots" />
    </Head>
);
