import { getSettingsQuery } from './_queries/getSettingsQuery';
import TransServer from 'app/_utils/translation/TransServer';
import { Link, linkPlaceholderTwClass } from 'components/Basic/Link/Link';
import { SkeletonPageHome } from 'components/Blocks/Skeleton/SkeletonPageHome';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

const HomePage = () => {
    return (
        <Webline>
            <Suspense fallback={<SkeletonPageHome />}>
                <h1>HOME PAGE</h1>
                <p>This is the home page.</p>
            </Suspense>
        </Webline>
    );
};

export default HomePage;
