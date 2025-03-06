import { LastVisitedProductsContent } from './LastVisitedProductsContent';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export type LastVisitedProductsProps = {
    currentProductCatnum?: string;
};

export const LastVisitedProducts = async ({ currentProductCatnum }: LastVisitedProductsProps) => {
    const { lastVisitedProductsCatnums } = getCookieStoreStateFromServer();

    if (!lastVisitedProductsCatnums) {
        return null;
    }

    const lastVisitedProductsWithoutCurrentProduct = lastVisitedProductsCatnums.filter(
        (lastVisitedProduct) => lastVisitedProduct !== currentProductCatnum,
    );

    return (
        <Suspense
            fallback={
                <Webline>
                    <SkeletonModuleLastVisitedProducts />
                </Webline>
            }
        >
            <Webline>
                <LastVisitedProductsContent productsCatnums={lastVisitedProductsWithoutCurrentProduct} />
            </Webline>
        </Suspense>
    );
};
