import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonPageResetPassword: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <SkeletonModulePageHero />

                <Skeleton className="h-[120px] rounded-xl" />

                <Skeleton className="mx-auto h-9 w-32" />
            </VerticalStack>
        </Webline>
    </>
);
