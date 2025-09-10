import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

const productListTwClass = 'grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5';

export const SkeletonModuleProductList: FC = () => (
    <div className={productListTwClass}>
        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
            <SkeletonModuleProductListItem key={index} />
        ))}
    </div>
);
