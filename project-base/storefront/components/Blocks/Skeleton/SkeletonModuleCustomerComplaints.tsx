import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerComplaints: FC = () => {
    return (
        <VerticalStack gap="sm">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="h-64 vl:h-40 rounded-xl sm:h-52" />
            ))}
        </VerticalStack>
    );
};
