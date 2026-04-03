import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerComplaints: FC = () => {
    return (
        <div className="flex flex-col gap-5">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="h-64 vl:h-32 rounded-xl sm:h-52" />
            ))}
        </div>
    );
};
