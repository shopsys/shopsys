import { SkeletonModuleBreadcrumbs } from 'components/Blocks/Skeleton/SkeletonModuleBreadcrumbs';

const BreadcrumbsLoading = () => {
    return (
        <div className="mt-4">
            <SkeletonModuleBreadcrumbs count={3} />
        </div>
    );
};

export default BreadcrumbsLoading;
