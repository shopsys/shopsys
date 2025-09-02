import { ComplaintItem } from './ComplaintItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';

type ComplaintsContentProps = {
    areComplaintsFetching: boolean;
    items: TypeComplaintDetailFragment[] | undefined;
    totalCount: number | undefined;
    hasNextPage: boolean | undefined;
};

export const ComplaintsContent: FC<ComplaintsContentProps> = ({
    areComplaintsFetching,
    items,
    totalCount,
    hasNextPage,
}) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { t } = useTranslation();

    if (!items?.length && !areComplaintsFetching) {
        return (
            <div className="vl:text-xl flex gap-2 text-lg">
                <InfoIcon className="w-5" />
                {t('You have no complaints')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            {areComplaintsFetching ? (
                <SkeletonModuleCustomerComplaints />
            ) : (
                <div className="flex flex-col gap-5">
                    {items?.map((item) => <ComplaintItem key={item.uuid} complaintItem={item} />)}
                </div>
            )}

            <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={totalCount || 0} />
            </PaginationProvider>
        </div>
    );
};
