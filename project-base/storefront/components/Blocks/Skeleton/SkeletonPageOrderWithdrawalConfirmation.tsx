import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageOrderWithdrawalConfirmation: FC = () => (
    <Webline>
        <div className="mb-4 lg:mt-6">
            <SkeletonModulePageHero />
        </div>

        <Skeleton className="my-4 h-32 rounded-xl" />
    </Webline>
);
