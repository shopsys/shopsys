'use client';

import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerOrders: FC = () => {
    return (
        <div className="mt-8 flex flex-col gap-7 lg:mt-4 vl:mt-12">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="h-[260px] !rounded-md sm:h-[212px] vl:h-[156px]" />
            ))}
        </div>
    );
};
