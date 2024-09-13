import { OrderedItem } from './OrderedItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { TypeOrderDetailItemFragment } from 'graphql/requests/orders/fragments/OrderDetailItemFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';

type OrderedItemsContentProps = {
    isFetching: boolean;
    items: TypeOrderDetailItemFragment[] | undefined;
    totalCount: number | undefined;
};

export const OrderedItemsContent: FC<OrderedItemsContentProps> = ({ isFetching, items, totalCount }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { t } = useTranslation();

    if (isFetching) {
        return <SkeletonModuleCustomerComplaints />;
    }

    if (!items?.length) {
        return (
            <div className="text-lg vl:text-xl flex gap-2">
                <InfoIcon className="w-5" />
                {t('You have no ordered items')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            <div className="flex flex-col gap-7">
                {items.map((item) => (
                    <OrderedItem key={item.uuid} orderedItem={item} />
                ))}
            </div>
            <Pagination paginationScrollTargetRef={paginationScrollTargetRef} totalCount={totalCount || 0} />
        </div>
    );
};
