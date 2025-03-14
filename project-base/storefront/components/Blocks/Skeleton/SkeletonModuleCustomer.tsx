import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomer: FC = ({ children }) => (
    <div className="flex flex-row items-stretch gap-10">
        <Skeleton className="h-[500px]" containerClassName="hidden lg:block w-[275px]" />

        {children}
    </div>
);
