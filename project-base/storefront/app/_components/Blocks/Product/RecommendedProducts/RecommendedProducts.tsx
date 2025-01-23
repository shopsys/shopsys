import { RecommendedProductsContent } from 'app/_components/Blocks/Product/RecommendedProducts/RecommendedProductsContent';
import { SkeletonModuleRecommendedProducts } from 'components/Blocks/Skeleton/SkeletonModuleRecommendedProducts';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeRecommendationType } from 'graphql/types';
import { headers } from 'next/headers';
import { Suspense } from 'react';
import { getDomainConfig } from 'utils/domain/domainConfig';

export type RecommendedProductsProps = {
    recommendationType: TypeRecommendationType;
    itemUuids?: string[];
};

export const RecommendedProducts: FC<RecommendedProductsProps> = ({ recommendationType, itemUuids = [] }) => {
    const { isLuigisBoxActive } = getDomainConfig(headers().get('host')!);

    if (!isLuigisBoxActive) {
        return null;
    }

    return (
        <Webline>
            <Suspense fallback={<SkeletonModuleRecommendedProducts />}>
                <RecommendedProductsContent itemUuids={itemUuids} recommendationType={recommendationType} />
            </Suspense>
        </Webline>
    );
};
