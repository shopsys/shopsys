import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeComplaintOrderedItemFragment } from 'graphql/requests/complaints/fragments/ComplaintOrderedItemFragment.generated';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { OrderedItem } from './OrderedItem';

type OrderedItemsContentProps = {
    areOrderedItemsFetching: boolean;
    items: TypeComplaintOrderedItemFragment[] | undefined;
    totalCount: number | undefined;
};

export const OrderedItemsContent: FC<OrderedItemsContentProps> = ({ areOrderedItemsFetching, items, totalCount }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { t } = useTranslation();

    if (!items?.length && !areOrderedItemsFetching) {
        return (
            <div className="flex gap-2 text-lg vl:text-xl">
                <InfoIcon className="w-5" />
                {t('You have no ordered items')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-fixed-header" ref={paginationScrollTargetRef}>
            {areOrderedItemsFetching ? (
                <SkeletonModuleCustomerComplaints />
            ) : (
                <div className="flex flex-col gap-5">
                    {items?.map((item) => (
                        <OrderedItem key={item.uuid} orderedItem={item} />
                    ))}
                </div>
            )}

            <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                <Pagination totalCount={totalCount || 0} />
            </PaginationProvider>
        </div>
    );
};
