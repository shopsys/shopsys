import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const AutocompleteSkeleton: FC = () => {
    return (
        <>
            <div>
                <Skeleton className="mb-2.5 h-6" />

                <div className="flex w-full gap-4 overflow-hidden lg:grid lg:grid-cols-4 lg:gap-5">
                    {createEmptyArray(4).map((_, index) => (
                        <Skeleton key={index} className="h-full min-h-[220px]" />
                    ))}
                </div>
            </div>

            <div>
                <Skeleton className="mb-2.5 h-6" />

                <div className="flex flex-wrap gap-2">
                    {createEmptyArray(3).map((_, index) => (
                        <Skeleton key={index} className="h-[28px] !rounded-full" />
                    ))}
                </div>
            </div>

            <div className="flex justify-center">
                <Skeleton className="h-[56px]" />
            </div>
        </>
    );
};
