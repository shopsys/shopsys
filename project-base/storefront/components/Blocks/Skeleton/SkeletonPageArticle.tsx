import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';

export const SkeletonPageArticle: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="vl">
            <VerticalStack gap="sm">
                <Skeleton className="h-10 w-1/2" />
                <Skeleton className="h-5 w-28 rounded-sm" />
                <Skeleton className="h-96" />
            </VerticalStack>
        </Webline>
    </>
);
