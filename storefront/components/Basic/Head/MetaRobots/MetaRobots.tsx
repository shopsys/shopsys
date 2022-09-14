import Head from 'next/head';
import { FC } from 'react';

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
        <meta name="robots" content={content} />
    </Head>
);
