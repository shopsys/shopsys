import { LastVisitedProductsContent } from './LastVisitedProductsContent';
import { getCookieStoreStateFromServer } from 'app/_utils/getCookieStoreStateFromServer';
import { SkeletonModuleLastVisitedProducts } from 'components/Blocks/Skeleton/SkeletonModuleLastVisitedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { useTranslation } from 'components/providers/TranslationProvider';
import { Suspense } from 'react';

export type LastVisitedProductsProps = {
    currentProductCatnum?: string;
};

export async function LastVisitedProducts({ currentProductCatnum }: LastVisitedProductsProps) {
    const { t } = useTranslation();
    const { lastVisitedProductsCatnums } = getCookieStoreStateFromServer();

    if (!lastVisitedProductsCatnums) {
        return null;
    }

    const lastVisitedProductsWithoutCurrentProduct = lastVisitedProductsCatnums.filter(
        (lastVisitedProduct) => lastVisitedProduct !== currentProductCatnum,
    );

    return (
        <Webline>
            <h2 className="h5 mb-3">{t('Last visited products')}</h2>

            <Suspense fallback={<SkeletonModuleLastVisitedProducts />}>
                <LastVisitedProductsContent productsCatnums={lastVisitedProductsWithoutCurrentProduct} />
            </Suspense>
        </Webline>
    );
}
