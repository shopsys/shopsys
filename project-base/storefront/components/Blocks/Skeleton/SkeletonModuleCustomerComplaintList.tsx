import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintList: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col">
                    <Skeleton className="h-11 w-72 mb-4" />
                    <Skeleton className="h-8 w-36 mb-4" />
                    <Skeleton className="h-10 w-full mb-4" />

                    <Skeleton className="h-36 w-full mb-5" />
                    <Skeleton className="h-36 w-full mb-5" />
                    <Skeleton className="h-36 w-full mb-5" />
                </div>
            </div>
        </div>
    </div>
);
