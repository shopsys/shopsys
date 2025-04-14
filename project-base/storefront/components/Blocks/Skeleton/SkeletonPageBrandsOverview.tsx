import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { Webline } from 'components/Layout/Webline/Webline';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonPageBrandsOverview: FC = () => (
    <Webline>
        <div className="grid gap-3 md:grid-cols-[repeat(auto-fill,minmax(210px,1fr))]">
            {createEmptyArray(23).map((_, index) => (
                <Skeleton key={index} className="h-20" />
            ))}
        </div>
    </Webline>
);
