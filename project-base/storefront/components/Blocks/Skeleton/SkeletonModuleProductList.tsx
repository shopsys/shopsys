import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { productListTwClass } from 'components/Pages/CategoryDetail/CategoryDetailProductsWrapper/CategoryDetailProductsWrapperPlaceholder';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleProductList: FC = () => (
    <div className={productListTwClass}>
        {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
            <SkeletonModuleProductListItem key={index} />
        ))}
    </div>
);
