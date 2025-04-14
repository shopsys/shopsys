import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleProductsList } from './SkeletonModuleProductsList';

export const SkeletonPageProductsList: FC = () => (
    <>
        <SkeletonModuleBreadcrumbs count={3} />

        <SkeletonModuleProductsList />
    </>
);
