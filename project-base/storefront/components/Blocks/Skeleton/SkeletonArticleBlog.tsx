import Skeleton from 'react-loading-skeleton';

export const SkeletonArticleBlog: FC = () => (
    <div className="flex h-36 w-full gap-4">
        <div className="h-full w-full">
            <Skeleton className="block h-full" />
        </div>
    </div>
);
