import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleOrderDetail } from './SkeletonModuleOrderDetail';

export const SkeletonPageOrderDetailByHash: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <Webline width="lg">
            <VerticalStack gap="sm">
                <SkeletonModuleOrderDetail />
            </VerticalStack>
        </Webline>
    </>
);
