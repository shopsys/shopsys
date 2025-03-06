import { PromotedProductsContent } from './PromotedProductsContent';
import { SkeletonModulePromotedProducts } from 'components/Blocks/Skeleton/SkeletonModulePromotedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { Suspense } from 'react';

export const PromotedProducts = async () => {
    return (
        <Suspense
            fallback={
                <Webline>
                    <SkeletonModulePromotedProducts />
                </Webline>
            }
        >
            <Webline>
                <PromotedProductsContent />
            </Webline>
        </Suspense>
    );
};
