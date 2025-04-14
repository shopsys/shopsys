import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';

export const SkeletonModuleCustomer: FC = ({ children }) => (
    <Webline>
        <div className="flex flex-row items-stretch gap-10">
            <Skeleton className="hidden h-[500px] w-[275px] rounded-xl lg:block" />

            <div className="flex-1">
                <VerticalStack gap="sm">{children}</VerticalStack>
            </div>
        </div>
    </Webline>
);
