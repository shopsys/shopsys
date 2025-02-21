import Skeleton from 'react-loading-skeleton';

export const SkeletonModuleCustomer: FC = ({ children }) => (
    <div className="vl:mt-12 mt-8 flex flex-row items-stretch gap-5 lg:mt-4">
        <Skeleton className="h-[300px] w-[300px]" containerClassName="hidden lg:block" />

        {children}
    </div>
);
