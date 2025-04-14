import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleProductsList } from './SkeletonModuleProductsList';

export const SkeletonPageFlag: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleProductsList isWithoutBestsellers isWithoutDescription isWithoutNavigation />
    </>
);
