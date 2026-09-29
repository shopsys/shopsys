import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { createEmptyArray } from 'utils/arrays/createEmptyArray';

export const SkeletonModuleArticleParagraphs: FC = () => (
    <div className="flex flex-col gap-4">
        {createEmptyArray(3).map((_, index) => (
            <div key={index} className="flex flex-col gap-2">
                <Skeleton className="h-7 w-4/5" />
                <Skeleton className="h-7" />
                <Skeleton className="h-7 w-11/12" />
                <Skeleton className="h-7 w-2/3" />
            </div>
        ))}
    </div>
);
