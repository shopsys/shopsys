import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageCart: FC = () => (
    <Webline>
        <Skeleton
            className="vl:h-11 mx-auto mt-1 mb-5 h-16 w-full max-w-[840px] lg:mt-6 lg:mb-10"
            containerClassName="flex"
        />

        <div className="flex flex-col gap-4">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="vl:h-32 h-60 w-full" />
            ))}
        </div>

        <div className="vl:mt-8 vl:flex-row vl:justify-between mt-5 flex flex-col gap-8">
            <Skeleton className="h-20 w-full" containerClassName="vl:max-w-[424px] w-full" />
            <Skeleton className="h-52 w-full" containerClassName="vl:max-w-[495px] w-full w-full" />
        </div>
    </Webline>
);
