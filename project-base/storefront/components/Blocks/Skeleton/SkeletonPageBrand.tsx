import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleProductsList } from './SkeletonModuleProductsList';

export const SkeletonPageBrand: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleProductsList isWithoutBestsellers isWithoutNavigation />
    </>
);
