import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageLogin: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <div className="mx-auto flex w-full max-w-3xl flex-col">
            <Skeleton className="h-10 w-72" containerClassName="flex mb-5" />
            <Skeleton className="h-[435px]" containerClassName="flex w-full" />
        </div>
    </Webline>
);
