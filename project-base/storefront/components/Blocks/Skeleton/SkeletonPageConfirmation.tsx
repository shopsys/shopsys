import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageConfirmation: FC = () => (
    <Webline width="xl">
        <div className="mb-4 lg:mt-6">
            <SkeletonModulePageHero />
        </div>

        <Skeleton className="my-4 h-32 rounded-card lg:my-10 lg:h-20" />

        <div className="grid vl:grid-cols-3 gap-4 vl:gap-10">
            <div className="vl:col-span-2 flex flex-col gap-4">
                <Skeleton className="h-44 rounded-card" />
                <Skeleton className="h-72 rounded-card" />
            </div>

            <div className="vl:col-span-1">
                <Skeleton className="h-72 rounded-card" />
            </div>
        </div>
    </Webline>
);
