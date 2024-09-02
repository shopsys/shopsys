import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerComplaintDetail: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col gap-4">
                    <Skeleton className="h-11 w-96 mb-5" />
                    <Skeleton className="h-16 w-full" />
                    <Skeleton className="h-80 w-full" />

                    <div className="flex flex-col vl:grid vl:grid-cols-3 w-full gap-6 my-6">
                        <Skeleton className="h-24 w-full" />
                        <Skeleton className="h-24 w-full" />
                    </div>
                </div>
            </div>
        </div>
    </div>
);
