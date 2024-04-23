import Skeleton from 'react-loading-skeleton';

export const SkeletonOrderContent: FC = () => (
    <>
        <div className="w-full mb-6 border-b border-graySlate p-0 lg:mb-3">
            <Skeleton className="w-full h-8" />
        </div>

        <div className="mb-24 flex w-full flex-col flex-wrap vl:mt-7 vl:mb-16 vl:flex-row">
            <div className="mb-16 w-full vl:mb-0 vl:min-h-[61vh] vl:flex-1 vl:pr-10">
                <Skeleton className="h-64 w-full" />
                <Skeleton className="h-56 w-full" />
            </div>
            <div className="w-full vl:max-w-md">
                <Skeleton className="w-full h-40" />
            </div>
        </div>
    </>
);
