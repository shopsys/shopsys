import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { getFieldHeightClassName } from 'components/Forms/Lib/fieldUtils';
import { twJoin } from 'tailwind-merge';
import { SkeletonModuleCustomer } from './SkeletonModuleCustomer';
import { SkeletonModuleCustomerOrders } from './SkeletonModuleCustomerOrders';
import { SkeletonModulePageHero } from './SkeletonModulePageHero';

export const SkeletonModuleCustomerOrderList: FC = () => (
    <SkeletonModuleCustomer>
        <SkeletonModulePageHero />

        <div className="rounded-xl bg-skeleton-less p-4">
            <div className="grid vl:grid-cols-2 gap-3 xl:grid-cols-[2fr_1fr_1fr]">
                <Skeleton className={twJoin('rounded-input', getFieldHeightClassName())} />
                <Skeleton className={twJoin('rounded-input', getFieldHeightClassName())} />
                <Skeleton className={twJoin('rounded-input', getFieldHeightClassName())} />
            </div>
        </div>

        <div className="flex min-w-0 flex-1 gap-3 overflow-hidden">
            <Skeleton className="h-8 vl:w-28 w-20 shrink-0 rounded-2xl" />
            <Skeleton className="h-8 vl:w-32 w-16 shrink-0 rounded-2xl" />
            <Skeleton className="h-8 vl:w-40 w-20 shrink-0 rounded-2xl" />
        </div>

        <SkeletonModuleCustomerOrders />
    </SkeletonModuleCustomer>
);
