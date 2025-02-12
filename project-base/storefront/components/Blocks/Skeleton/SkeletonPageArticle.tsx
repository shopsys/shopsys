import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageArticle: FC = () => (
    <>
        <Webline>
            <SkeletonModuleBreadcrumbs count={2} />
        </Webline>

        <Webline width="md">
            <div className="flex flex-col gap-4">
                <Skeleton className="h-10" containerClassName="w-1/2" />
                <Skeleton className="h-5" containerClassName="w-28" />
                <Skeleton className="h-96" containerClassName="w-full" />
            </div>
        </Webline>
    </>
);
