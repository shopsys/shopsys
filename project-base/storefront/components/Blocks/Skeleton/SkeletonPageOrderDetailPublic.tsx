import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageOrderDetailPublic: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <SkeletonModulePageHero />

                <Skeleton className="h-21 rounded-xl" />
                <Skeleton className="h-32 rounded-xl" />
                <Skeleton className="h-22 rounded-xl" />

                <Skeleton className="h-64" />

                <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-xl bg-skeleton-less p-5 lg:grid-cols-2">
                    <Skeleton className="h-44 rounded-xl" />
                    <Skeleton className="h-44 rounded-xl" />
                    <Skeleton className="h-44 rounded-xl" />
                </div>
            </VerticalStack>
        </Webline>
    </>
);
