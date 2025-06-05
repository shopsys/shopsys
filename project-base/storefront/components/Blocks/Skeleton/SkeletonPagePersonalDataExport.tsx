import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonPagePersonalDataExport: FC = () => (
    <Webline>
        <VerticalStack gap="sm">
            <Skeleton className="h-8 w-72 lg:h-10" />
            <Skeleton className="h-9 w-36" />

            <Skeleton className="h-8 w-72 lg:h-10" />
        </VerticalStack>
    </Webline>
);
