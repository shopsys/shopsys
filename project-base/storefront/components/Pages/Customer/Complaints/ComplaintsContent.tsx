import { ComplaintItem } from './ComplaintItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';

type ComplaintsContentProps = {
    isFetching: boolean;
    items: TypeComplaintDetailFragment[] | undefined;
    totalCount: number | undefined;
};

export const ComplaintsContent: FC<ComplaintsContentProps> = ({ isFetching, items, totalCount }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { t } = useTranslation();

    if (isFetching) {
        return <SkeletonModuleCustomerComplaints />;
    }

    if (!items?.length) {
        return (
            <div className="vl:text-xl flex gap-2 text-lg">
                <InfoIcon className="w-5" />
                {t('You have no complaints')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            <div className="flex flex-col gap-5">
                {items.map((item) => (
                    <ComplaintItem key={item.uuid} complaintItem={item} />
                ))}
            </div>

            <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                <Pagination totalCount={totalCount || 0} />
            </PaginationProvider>
        </div>
    );
};
