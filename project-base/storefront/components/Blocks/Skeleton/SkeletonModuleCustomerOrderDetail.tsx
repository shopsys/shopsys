import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerOrderDetail: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col gap-4">
                    <Skeleton className="mb-5 h-11 w-96" />
                    <Skeleton className="h-14 w-full" />
                    <Skeleton className="h-24 w-full" />
                    <Skeleton className="h-14 w-full" />
                    <Skeleton className="h-32 w-full" />

                    <div className="my-6 flex w-full flex-col gap-6 vl:grid vl:grid-cols-3">
                        <Skeleton className="h-24 w-full" />
                        <Skeleton className="h-24 w-full" />
                        <Skeleton className="h-24 w-full" />
                    </div>
                </div>
            </div>
        </div>
    </div>
);
