import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerComplaints: FC = () => {
    return (
        <div className="flex flex-col gap-5">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="vl:h-32 h-64 rounded-xl sm:h-52" />
            ))}
        </div>
    );
};
