import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModulePromotedProducts: FC = () => (
    <Webline>
        <Skeleton className="h-[470px] rounded-xl" />
    </Webline>
);
