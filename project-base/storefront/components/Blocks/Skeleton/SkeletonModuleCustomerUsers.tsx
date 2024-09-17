import Skeleton from 'react-loading-skeleton';

export const SkeletonCustomerUsersTable: FC = () => (
    <>
        <Skeleton className="h-12 w-full mb-0.5" />
        <Skeleton className="h-12 w-full mb-0.5" />
        <Skeleton className="h-12 w-full mb-0.5" />
    </>
);

export const SkeletonModuleCustomerUsers: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col">
                    <Skeleton className="h-10 w-40 mb-4" />
                    <Skeleton className="h-8 w-36 mb-4" />

                    <SkeletonCustomerUsersTable />
                </div>
            </div>
        </div>
    </div>
);
