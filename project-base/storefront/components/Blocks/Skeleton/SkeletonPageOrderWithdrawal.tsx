import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';

export const SkeletonPageOrderWithdrawal: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <Skeleton className="h-8 w-80 lg:h-10" />

                <Skeleton className="h-6 w-48" />

                <Skeleton className="h-125 rounded-xl" />
            </VerticalStack>
        </Webline>
    </>
);
