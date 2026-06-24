import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';

type SkeletonModulePageHeroProps = {
    simple?: boolean;
};

export const SkeletonModulePageHero: FC<SkeletonModulePageHeroProps> = ({ simple = false }) => (
    <VerticalStack gap="xs">
        <Skeleton className="mx-auto size-14 rounded-full" />

        <Skeleton className="mx-auto h-7 w-72 lg:h-8" />

        {!simple && (
            <div className="flex flex-col items-center justify-center gap-2">
                <Skeleton className="h-5 w-2/4" />
                <Skeleton className="h-5 w-3/5" />
            </div>
        )}
    </VerticalStack>
);
