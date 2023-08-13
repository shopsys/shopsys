import Skeleton from 'react-loading-skeleton';

export const ProductListItemSkeleton: FC = () => (
    <div className="flex w-full flex-col border-b border-greyLighter pb-5 lg:py-4 lg:px-5">
        <Skeleton
            className="h-full rounded-none lg:mb-24"
            containerClassName="h-[180px] mb-2 lg:h-[179px] lg:mb-24 lg:max-w-[160px] w-full"
        />
        <Skeleton className="mb-6 h-6 w-5/6 lg:mb-4 lg:h-4" containerClassName="w-2/3 lg:w-full" />
        <Skeleton className="h-6 w-16 lg:h-4" containerClassName="w-1/3 lg:w-full" />
        <Skeleton className="mt-2 mb-10 h-3 w-4/6 lg:mb-4 lg:h-5" containerClassName="w-1/2 lg:w-full" />
        <Skeleton className="h-10 w-32" />
    </div>
);
