import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleHeadline } from './SkeletonModuleHeadline';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageRegistration: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <SkeletonModuleHeadline />

                <Skeleton className="h-screen w-full" />
            </VerticalStack>
        </Webline>
    </>
);
