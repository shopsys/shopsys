import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModulePromotedCategories: FC = () => (
    <Webline>
        <Skeleton className="h-[245px] vl:h-[635px] rounded-xl" />
    </Webline>
);
