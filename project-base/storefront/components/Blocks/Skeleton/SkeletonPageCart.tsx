import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';
import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';

export const SkeletonPageCart: FC = () => (
    <>
        <Webline width="xl">
            <Skeleton className="mx-auto mt-1 mb-5 flex h-16 vl:h-11 w-full max-w-210 rounded-xl lg:mt-6 lg:mb-10" />

            <div className="flex flex-col gap-4">
                {createEmptyArray(3).map((_, index) => (
                    <Skeleton key={index} className="h-60 vl:h-32 w-full rounded-xl" />
                ))}
            </div>

            <div className="mt-5 vl:mt-8 flex vl:flex-row flex-col vl:justify-between gap-8">
                <Skeleton className="h-20 w-full vl:max-w-106 rounded-xl" />
                <Skeleton className="h-52 w-full vl:max-w-123.75 rounded-xl" />
            </div>
        </Webline>

        <Webline>
            <SkeletonModuleProductSlider />
        </Webline>
    </>
);
