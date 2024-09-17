import Skeleton from 'react-loading-skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerOrders: FC = () => {
    return (
        <div className="flex flex-col gap-7">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="h-[260px] !rounded-md sm:h-[212px] vl:h-[156px]" />
            ))}
        </div>
    );
};
