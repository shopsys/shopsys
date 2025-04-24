import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPageContact: FC = () => (
    <Webline width="lg">
        <VerticalStack gap="sm">
            <Skeleton className="h-8 w-72 lg:h-10" />

            <div className="flex flex-col gap-1">
                <Skeleton className="h-6" />
                <Skeleton className="h-6 w-2/3" />
            </div>

            <Skeleton className="h-[440px] rounded-xl" />
        </VerticalStack>
    </Webline>
);
