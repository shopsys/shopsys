import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomer: FC = ({ children }) => (
    <div className="flex flex-row items-stretch gap-5">
        <Skeleton className="size-full" containerClassName="hidden lg:block w-[300px] h-[340px]" />

        {children}
    </div>
);
