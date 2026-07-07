import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleCustomerOrders: FC = () => {
    return (
        <VerticalStack gap="sm">
            {createEmptyArray(3).map((_, index) => (
                <Skeleton key={index} className="h-115 vl:h-72 rounded-xl" />
            ))}
        </VerticalStack>
    );
};
