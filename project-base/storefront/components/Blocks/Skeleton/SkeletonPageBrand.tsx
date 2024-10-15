import { SkeletonModuleBreadcrumbs } from './SkeletonModuleBreadcrumbs';
import { SkeletonModuleProductsList } from './SkeletonModuleProductsList';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageBrand: FC = () => (
    <Webline>
        <SkeletonModuleBreadcrumbs count={2} />

        <SkeletonModuleProductsList isWithoutBestsellers isWithoutNavigation />
    </Webline>
);
