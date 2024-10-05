import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintList: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col">
                    <Skeleton className="mb-4 h-11 w-72" />
                    <Skeleton className="mb-4 h-8 w-36" />
                    <Skeleton className="mb-4 h-10 w-full" />

                    <Skeleton className="mb-5 h-36 w-full" />
                    <Skeleton className="mb-5 h-36 w-full" />
                    <Skeleton className="mb-5 h-36 w-full" />
                </div>
            </div>
        </div>
    </div>
);
