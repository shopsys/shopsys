import { LastVisitedProductsContent } from './LastVisitedProductsContent';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { Suspense } from 'react';

export type LastVisitedProductsProps = {
    currentProductCatnum?: string;
};

export const LastVisitedProducts = async ({ currentProductCatnum }: LastVisitedProductsProps) => {
    const { lastVisitedProductsCatnums } = await getCookieStoreStateFromServer();

    if (!lastVisitedProductsCatnums) {
        return null;
    }

    const lastVisitedProductsWithoutCurrentProduct = lastVisitedProductsCatnums.filter(
        (lastVisitedProduct) => lastVisitedProduct !== currentProductCatnum,
    );

    return (
        <Suspense
            fallback={
                <section>
                    <SkeletonModuleLastVisitedProducts />
                </section>
            }
        >
            <LastVisitedProductsContent productsCatnums={lastVisitedProductsWithoutCurrentProduct} />
        </Suspense>
    );
};
