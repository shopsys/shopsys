import Skeleton from 'react-loading-skeleton';

export const OrderLayoutContentSkeleton: FC = ({ children }) => {
    return (
        <>
            <Skeleton className="h-10 w-full" />

            <div className="mb-24 flex w-full flex-col flex-wrap vl:mt-7 vl:mb-16 vl:flex-row">
                <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">{children}</div>
                <div className="h-60 w-full vl:max-w-md">
                    <Skeleton className="h-full w-full" />
                </div>
            </div>
        </>
    );
};
