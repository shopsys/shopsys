import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModulePromotedCategories: FC = () => (
    <Webline>
        <Skeleton className="vl:h-[635px] h-[245px] rounded-xl" />
    </Webline>
);
