import { ComplaintItem } from './ComplaintItem';
import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
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
            <div className="flex gap-2 text-lg vl:text-xl">
                <InfoIcon className="w-5" />
                {t('You have no complaints')}
            </div>
        );
    }

    return (
        <div className="scroll-mt-5" ref={paginationScrollTargetRef}>
            <div className="flex flex-col gap-7">
                {items.map((item) => (
                    <ComplaintItem key={item.uuid} complaintItem={item} />
                ))}
            </div>
            <Pagination paginationScrollTargetRef={paginationScrollTargetRef} totalCount={totalCount || 0} />
        </div>
    );
};
