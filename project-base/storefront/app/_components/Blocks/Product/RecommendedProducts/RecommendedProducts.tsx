import { RecommendedProductsContent } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProductsContent';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import { SkeletonModuleRecommendedProducts } from 'components/Blocks/Skeleton/SkeletonModuleRecommendedProducts';
import { TypeRecommendationType } from 'graphql/types';
import { headers } from 'next/headers';
import { Suspense } from 'react';

export type RecommendedProductsProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
};

export const RecommendedProducts: FC<RecommendedProductsProps> = async ({ recommendationType, itemUuids = [] }) => {
    const { isLuigisBoxActive } = getDomainConfig((await headers()).get('host')!);

    if (!isLuigisBoxActive) {
        return null;
    }

    return (
        <Suspense
            fallback={
                <section>
                    <SkeletonModuleRecommendedProducts />
                </section>
            }
        >
            <RecommendedProductsContent itemUuids={itemUuids} recommendationType={recommendationType} />
        </Suspense>
    );
};
