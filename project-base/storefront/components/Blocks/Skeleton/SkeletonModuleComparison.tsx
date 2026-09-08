import { Skeleton } from 'components/Basic/Skeleton/Skeleton';

export const SkeletonModuleComparison: FC = () => (
    <div aria-hidden="true" className="mx-auto w-full max-w-318 overflow-hidden rounded-lg border border-border-less">
        <div className="grid grid-cols-2 gap-4 p-4 md:grid-cols-[12rem_repeat(3,minmax(0,1fr))]">
            <div className="hidden md:block" />
            {[0, 1, 2].map((index) => (
                <Skeleton key={index} className={index === 2 ? 'hidden h-80 md:block' : 'h-80'} />
            ))}
        </div>
        <div className="border-border-less border-t p-4">
            <Skeleton className="h-5 w-40" />
        </div>
        <div className="space-y-4 border-border-less border-t p-4">
            {[0, 1, 2].map((index) => (
                <Skeleton key={index} className="h-10 w-full" />
            ))}
        </div>
    </div>
);
