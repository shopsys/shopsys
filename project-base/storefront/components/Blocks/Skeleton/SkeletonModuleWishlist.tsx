import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleWishlist: FC = () => (
    <div className="xs:grid-cols-2 relative grid grid-cols-1 gap-2.5 sm:gap-x-5 sm:gap-y-6 lg:grid-cols-3 xl:grid-cols-4">
        {createEmptyArray(4).map((_, index) => (
            <SkeletonModuleProductListItem key={index} />
        ))}
    </div>
);
