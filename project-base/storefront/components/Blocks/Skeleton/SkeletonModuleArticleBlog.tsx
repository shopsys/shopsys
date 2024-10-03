import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleArticleBlog: FC = () => (
    <div className="flex h-64 w-full gap-4">
        <div className="h-full w-full">
            <Skeleton className="block h-full rounded-xl" />
        </div>
    </div>
);
