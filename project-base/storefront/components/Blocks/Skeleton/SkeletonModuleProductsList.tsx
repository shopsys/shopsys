import { SkeletonModuleFilterAndSortingBar } from './SkeletonModuleFilterAndSortingBar';
import { SkeletonModuleFilterPanel } from './SkeletonModuleFilterPanel';
import { SkeletonModuleProductListItem } from './SkeletonModuleProductListItem';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { productListTwClass } from 'components/Blocks/Product/ProductsList/ProductsList';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export type SkeletonModuleProductsListProps = {
    isWithoutDescription?: boolean;
    isWithoutNavigation?: boolean;
    isWithoutBestsellers?: boolean;
};

export const SkeletonModuleProductsList: FC<SkeletonModuleProductsListProps> = ({
    isWithoutDescription,
    isWithoutNavigation,
    isWithoutBestsellers = false,
}) => (
    <Webline>
        <VerticalStack gap="md">
            <div className="flex flex-col gap-4">
                <Skeleton className="h-8 w-72 lg:h-10" />

                {!isWithoutDescription && <Skeleton className="h-32" />}
            </div>

            {!isWithoutNavigation && (
                <div className="vl:grid-cols-5 grid gap-3 md:grid-cols-3 lg:grid-cols-4">
                    {createEmptyArray(5).map((_, index) => (
                        <Skeleton key={index} className="h-20" />
                    ))}
                </div>
            )}

            <div className="vl:flex-row vl:flex-wrap vl:gap-4 flex scroll-mt-5 flex-col">
                <SkeletonModuleFilterPanel />

                <div className="flex-1">
                    <VerticalStack gap="sm">
                        {!isWithoutBestsellers && <Skeleton className="h-96" />}

                        <SkeletonModuleFilterAndSortingBar />

                        <div className={productListTwClass}>
                            {createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => (
                                <SkeletonModuleProductListItem key={index} />
                            ))}
                        </div>
                    </VerticalStack>
                </div>
            </div>
        </VerticalStack>
    </Webline>
);
