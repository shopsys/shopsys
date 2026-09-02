import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageLogin: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="sm">
            <VerticalStack gap="sm">
                <SkeletonModulePageHero />

                <Skeleton className="h-100 w-full" />
            </VerticalStack>
        </Webline>
    </>
);
