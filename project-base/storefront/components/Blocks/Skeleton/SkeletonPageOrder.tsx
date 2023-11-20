import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageOrder: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={3} />
        <Skeleton className="mb-4 h-9 w-80 lg:mb-4" containerClassName="flex justify-center" />
        <Skeleton className="h-48" />
        <Skeleton className="mt-10 h-[596px]" />
        <Skeleton className="mt-10 mb-3 h-9 w-80" containerClassName="flex justify-center" />
        <Skeleton className="h-[596px]" />
    </Webline>
);
