import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModulePromotedCategories: FC = () => (
    <Webline>
        <Skeleton className="h-61.25 vl:h-85 rounded-xl" />
    </Webline>
);
