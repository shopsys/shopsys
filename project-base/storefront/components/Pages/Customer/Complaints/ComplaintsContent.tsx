import { InfoIcon } from 'components/Basic/Icon/InfoIcon';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ComplaintItem } from './ComplaintItem';

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
    const { t } = useTranslation();

    if (!items?.length && !areComplaintsFetching) {
        return (
            <div className="text-center">
                <InfoIcon className="mr-2 size-5" />
                {t('You have no complaints')}
            </div>
        );
    }

    return (
        <>
            {areComplaintsFetching ? (
                <SkeletonModuleCustomerComplaints />
            ) : (
                <div className="flex flex-col gap-5" data-tid={TIDs.complaints_list}>
                    {items?.map((item) => (
                        <ComplaintItem key={item.uuid} complaintItem={item} />
                    ))}
                </div>
            )}

            <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={totalCount || 0} />
        </>
    );
};
