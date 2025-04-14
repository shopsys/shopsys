import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleProductDetailAddToCart: FC = () => (
    <div className="flex items-center gap-2">
        <Skeleton className="h-14 w-32" />
        <Skeleton className="h-14 w-36" />
    </div>
);
