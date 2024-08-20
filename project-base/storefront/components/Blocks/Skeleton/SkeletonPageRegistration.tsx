import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageRegistration: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <div className="flex max-w-3xl w-full mx-auto flex-col">
            <Skeleton className="mb-3 h-10 w-80" />
            <Skeleton className="mb-3 h-screen w-full" />
        </div>
    </Webline>
);
