import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageCustomerAccount: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />
        <Skeleton className="mb-4 h-11 w-60 lg:mb-4" containerClassName="flex justify-center" />

        <div className="mb-5 flex flex-col gap-5 md:grid md:grid-cols-4">
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-16 w-full" />
            <Skeleton className="h-16 w-full" />
        </div>
    </Webline>
);
