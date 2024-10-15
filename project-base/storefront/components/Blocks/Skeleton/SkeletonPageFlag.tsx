import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleProductsList } from './SkeletonModuleProductsList';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageFlag: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleProductsList isWithoutBestsellers isWithoutDescription isWithoutNavigation />
    </Webline>
);
