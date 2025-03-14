import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const AutocompleteSkeleton: FC = () => {
    return (
        <>
            <div>
                <Skeleton className="mb-2.5 h-6" containerClassName="flex w-[100px]" />

                <div className="flex w-full gap-4 overflow-hidden lg:grid lg:grid-cols-4 lg:gap-5">
                    {createEmptyArray(4).map((_, index) => (
                        <Skeleton key={index} className="h-full min-h-[220px]" />
                    ))}
                </div>
            </div>

            <div>
                <Skeleton className="mb-2.5 h-6" containerClassName="flex w-[80px]" />

                <div className="flex flex-wrap gap-2">
                    {createEmptyArray(3).map((_, index) => (
                        <Skeleton key={index} className="h-[28px] !rounded-full" containerClassName="w-[120px]" />
                    ))}
                </div>
            </div>

            <div className="flex justify-center">
                <Skeleton className="h-[56px]" containerClassName="vl:w-[180px] w-full" />
            </div>
        </>
    );
};
