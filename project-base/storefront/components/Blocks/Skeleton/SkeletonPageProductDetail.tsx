'use client';

import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModulePromotedProducts } from './SkeletonModulePromotedProducts';
import { Container } from 'app/_components/Layout/Container/Container';
import Skeleton from 'react-loading-skeleton';

export const SkeletonPageProductDetail: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <Container>
            <div className="flex flex-col gap-6 lg:flex-row">
                <Skeleton
                    className="size-[500px]"
                    containerClassName="vl:basis-3/5 vl:flex-row flex w-full basis-1/2"
                />

                <div className="flex flex-1 flex-col gap-3">
                    <Skeleton className="h-8" containerClassName="w-72" />
                    <Skeleton className="h-96" containerClassName="w-full" />
                </div>
            </div>

            <div className="flex flex-col gap-3">
                <Skeleton className="h-8" containerClassName="w-72" />
                <SkeletonModulePromotedProducts />
            </div>

            <div className="flex flex-col gap-3">
                <Skeleton className="h-8" containerClassName="w-72" />
                <SkeletonModulePromotedProducts />
            </div>

            <div className="flex flex-col gap-3">
                <Skeleton className="h-8" containerClassName="w-72" />
                <SkeletonModulePromotedProducts />
            </div>
        </Container>
    </>
);
