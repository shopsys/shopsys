import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { createEmptyArray } from 'helpers/arrays/createEmptyArray';

export const SkeletonModuleWishlist: FC = () => (
    <div className="mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6">
        {createEmptyArray(4).map((_, index) => (
            <SkeletonModuleProductListItem key={index} />
        ))}
    </div>
);
