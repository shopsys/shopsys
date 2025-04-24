import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageCart: FC = () => (
    <Webline>
        <Skeleton className="vl:h-11 mx-auto mt-1 mb-5 flex h-16 w-full max-w-[840px] rounded-xl lg:mt-6 lg:mb-10" />

        <div className="flex flex-col gap-4">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="vl:h-32 h-60 w-full rounded-xl" />
            ))}
        </div>

        <div className="vl:mt-8 vl:flex-row vl:justify-between mt-5 flex flex-col gap-8">
            <Skeleton className="vl:max-w-[424px] h-20 w-full rounded-xl" />
            <Skeleton className="vl:max-w-[495px] h-52 w-full rounded-xl" />
        </div>
    </Webline>
);
