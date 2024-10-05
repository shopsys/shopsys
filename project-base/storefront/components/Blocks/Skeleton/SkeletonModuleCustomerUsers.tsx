import Skeleton from 'react-loading-skeleton';

export const SkeletonCustomerUsersTable: FC = () => (
    <>
        <Skeleton className="mb-0.5 h-12 w-full" />
        <Skeleton className="mb-0.5 h-12 w-full" />
        <Skeleton className="mb-0.5 h-12 w-full" />
    </>
);

export const SkeletonModuleCustomerUsers: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full">
            <div className="flex">
                <div className="flex w-full flex-col">
                    <Skeleton className="mb-4 h-10 w-40" />
                    <Skeleton className="mb-4 h-8 w-36" />

                    <SkeletonCustomerUsersTable />
                </div>
            </div>
        </div>
    </div>
);
