import { SkeletonModuleProductSlider } from './SkeletonModuleProductSlider';
import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageCart: FC = () => (
    <>
        <Skeleton className="w-full h-10 mb-6 lg:mb-3" />
        <div className="mb-6 lg:mb-8">
            {createEmptyArray(4).map((index) => (
                <Skeleton key={index} className="w-full h-[101px]" />
            ))}
        </div>
        <div className="flex flex-col vl:flex-row justify-between mb-8">
            <Skeleton className="w-full vl:w-52 h-10" />
            <Skeleton className="w-full vl:w-40 h-10" />
            <Skeleton className="w-full vl:w-44 h-10" />
        </div>
        <div className="flex flex-col lg:flex-row items-center justify-between mb-12 lg:mb-24">
            <Skeleton className="w-20 h-8 lg:h-14" containerClassName="order-2 lg:order-1" />
            <Skeleton className="w-64 h-14" containerClassName="order-1 lg:order-2 mb-8 lg:mb-0" />
        </div>
        <Skeleton className="w-60 lg:w-72 h-8 mb-3" />
        <SkeletonModuleProductSlider />
    </>
);
