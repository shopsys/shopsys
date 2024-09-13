import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomerEditProfile: FC = () => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        <div className="w-full max-w-3xl">
            <div className="flex w-full flex-col">
                <Skeleton className="h-11 w-72 mb-4" />

                <Skeleton className="h-[1000px] w-full" />
            </div>
        </div>
    </div>
);
