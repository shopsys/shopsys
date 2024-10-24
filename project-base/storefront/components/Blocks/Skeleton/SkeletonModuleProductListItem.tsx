import Skeleton from 'react-loading-skeleton';
import { twMergeCustom } from 'utils/twMerge';

type SkeletonModuleProductListItemProps = {
    isSimpleCard?: boolean;
};

export const SkeletonModuleProductListItem: FC<SkeletonModuleProductListItemProps> = ({ isSimpleCard }) => (
    <div className="flex w-full flex-col gap-2 px-2.5 py-5 sm:p-5">
        <Skeleton
            className="h-full w-10/12 lg:w-full"
            containerClassName={twMergeCustom(['h-[180px] w-full flex justify-center'])}
        />
        <div className="mb-2 flex flex-col">
            <Skeleton className="full h-3 lg:h-4" />
            <Skeleton className="h-3 w-4/6 lg:h-4" />
        </div>
        <Skeleton className="h-6 w-16 lg:h-7" containerClassName="w-1/3 lg:w-full" />
        {!isSimpleCard && (
            <div className="flex flex-col">
                <Skeleton className="h-3 w-5/6 lg:h-4" />
                <Skeleton className="h-3 w-4/6 lg:h-4" />
            </div>
        )}
        <Skeleton className="h-9 w-full" />
    </div>
);
