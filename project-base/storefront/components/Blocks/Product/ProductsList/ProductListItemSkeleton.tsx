import Skeleton from 'react-loading-skeleton';

export const ProductListItemSkeleton: FC = () => (
    <div className="flex w-full flex-col border-b border-greyLighter pb-5 lg:py-4 lg:px-5">
        <Skeleton className="mb-24 h-full rounded-none" containerClassName="h-[179px] mb-24 max-w-[160px] w-full" />
        <Skeleton className="mb-4 h-4 w-5/6" containerClassName="w-full" />
        <Skeleton className="h-4 w-16" />
        <Skeleton className="mt-2 mb-4 h-5  w-4/6" />
        <Skeleton className="h-10 w-32" />
    </div>
);
