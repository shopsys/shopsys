import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';
import { Webline } from 'components/Layout/Webline/Webline';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageCart: FC = () => (
    <Webline>
        <Skeleton className="mx-auto mb-5 mt-1 h-11 w-full max-w-[840px] lg:mb-10 lg:mt-6" containerClassName="flex" />

        <div className="mb-6 lg:mb-8">
            {createEmptyArray(4).map((_, index) => (
                <Skeleton key={index} className="h-[101px] w-full" />
            ))}
        </div>
        <div className="mb-8 flex flex-col justify-between vl:flex-row">
            <Skeleton className="h-10 w-full vl:w-52" />
            <Skeleton className="h-10 w-full vl:w-40" />
            <Skeleton className="h-10 w-full vl:w-44" />
        </div>
        <div className="mb-12 flex flex-col items-center justify-between lg:mb-24 lg:flex-row">
            <Skeleton className="h-8 w-20 lg:h-14" containerClassName="order-2 lg:order-1" />
            <Skeleton className="h-14 w-64" containerClassName="order-1 lg:order-2 mb-8 lg:mb-0" />
        </div>
        <Skeleton className="mb-3 h-8 w-60 lg:w-72" />
        <SkeletonModuleProductSlider />
    </Webline>
);
