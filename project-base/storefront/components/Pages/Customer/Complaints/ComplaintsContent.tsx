import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { SkeletonModuleCustomerComplaints } from 'components/Blocks/Skeleton/SkeletonModuleCustomerComplaints';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { CustomerEmptyContent } from 'components/Pages/Customer/CustomerEmptyContent';
import { DEFAULT_ORDERS_SIZE } from 'config/constants';
import { TIDs } from 'cypress/tids';
import { TypeComplaintListItemFragment } from 'graphql/requests/complaints/fragments/ComplaintListItemFragment.generated';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { ComplaintItem } from './ComplaintItem';

type ComplaintsContentProps = {
    areComplaintsFetching: boolean;
    items: TypeComplaintListItemFragment[] | undefined;
    filteredTotalCount: number | undefined;
    hasNextPage: boolean | undefined;
    hasActiveFilters: boolean;
    hasActiveStatus: boolean;
};

export const ComplaintsContent: FC<ComplaintsContentProps> = ({
    areComplaintsFetching,
    items,
    filteredTotalCount,
    hasNextPage,
    hasActiveFilters,
    hasActiveStatus,
}) => {
    const { t } = useTranslation();

    if (areComplaintsFetching || items === undefined) {
        return <SkeletonModuleCustomerComplaints />;
    }

    if (items.length === 0) {
        const emptyContent = getEmptyContent(t, hasActiveFilters, hasActiveStatus);

        return <CustomerEmptyContent title={emptyContent.title} description={emptyContent.description} />;
    }

    return (
        <>
            <VerticalStack gap="sm" data-tid={TIDs.complaints_list}>
                {items.map((item) => (
                    <ComplaintItem key={item.uuid} complaintItem={item} />
                ))}
            </VerticalStack>

            <Pagination hasNextPage={hasNextPage} pageSize={DEFAULT_ORDERS_SIZE} totalCount={filteredTotalCount || 0} />
        </>
    );
};

const getEmptyContent = (
    t: ReturnType<typeof useTranslation>['t'],
    hasActiveFilters: boolean,
    hasActiveStatus: boolean,
) => {
    if (hasActiveFilters) {
        return {
            title: t('No complaints match your filters'),
            description: t('Try adjusting or clearing your filters to see more complaints.'),
        };
    }

    if (hasActiveStatus) {
        return {
            title: t('No complaints in this status'),
            description: t('Try switching to another status to see more complaints.'),
        };
    }

    return {
        title: t('You have no complaints'),
        description: t('Your complaints will appear here after you create your first complaint.'),
    };
};
