import Skeleton from 'react-loading-skeleton';

export const BlogArticleSkeleton: FC = () => (
    <div className="flex h-36 w-full gap-4">
        <div className="h-full w-36">
            <Skeleton className="block h-full" />
        </div>
        <div className="h-full flex-1">
            <Skeleton className="block h-full" />
        </div>
    </div>
);
