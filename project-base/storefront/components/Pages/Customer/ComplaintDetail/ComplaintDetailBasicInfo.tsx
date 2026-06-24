import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { CustomerRecordCard, CustomerRecordColumnInfo } from 'components/Pages/Customer/CustomerRecordElements';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { twMergeCustom } from 'utils/twMerge';
import { ComplaintDetailComplaintItem } from './ComplaintDetailComplaintItem';

type ComplaintDetailBasicInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailBasicInfo: FC<ComplaintDetailBasicInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();
    const { url } = useDomainConfig();
    const { currentCustomerUserUuid, canViewCompanyOrders, canCreateOrder } = useAuthorization();
    const [customerOrderDetailUrl] = getInternationalizedStaticUrls(['/customer/order-detail'], url);
    const complaintOrder = complaint.order;
    const complaintOrderBelongsToCurrentCustomer = complaintOrder?.customerUser?.uuid === currentCustomerUserUuid;
    const hasAccessToOrder = canViewCompanyOrders || (canCreateOrder && complaintOrderBelongsToCurrentCustomer);
    const complaintDocumentNumber = complaintOrder?.number ?? complaint.manualDocumentNumber;

    return (
        <>
            <CustomerRecordCard className="gap-5">
                <CustomerRecordColumnInfo tid={TIDs.complaint_detail_number} title={t('Complaint number')}>
                    {complaint.number}
                </CustomerRecordColumnInfo>

                <CustomerRecordColumnInfo tid={TIDs.complaint_detail_creation_date} title={t('Creation date')}>
                    {formatDate(complaint.createdAt)}
                </CustomerRecordColumnInfo>

                <CustomerRecordColumnInfo title={t('Status')}>{complaint.status}</CustomerRecordColumnInfo>

                <CustomerRecordColumnInfo title={t('Resolution')}>{complaint.resolution.name}</CustomerRecordColumnInfo>

                {complaintDocumentNumber && (
                    <CustomerRecordColumnInfo
                        title={complaintOrder ? t('Order number') : t('Order or document number')}
                    >
                        {complaintOrder && hasAccessToOrder ? (
                            <ExtendedNextLink
                                className="text-sm"
                                type="orderDetail"
                                href={{
                                    pathname: customerOrderDetailUrl,
                                    query: { orderNumber: complaintOrder.number },
                                }}
                            >
                                {complaintOrder.number}
                            </ExtendedNextLink>
                        ) : (
                            complaintDocumentNumber
                        )}
                    </CustomerRecordColumnInfo>
                )}

                {isResolutionMoneyReturn(complaint.resolution) && complaint.bankAccountNumber && (
                    <CustomerRecordColumnInfo
                        tid={TIDs.complaint_detail_bank_account_number}
                        title={t('Bank account number')}
                        valueClassName={twMergeCustom(
                            'max-w-52 xxs:max-w-64 overflow-x-auto overflow-y-hidden whitespace-nowrap sm:max-w-fit',
                            '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most',
                            '[&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                        )}
                    >
                        {complaint.bankAccountNumber}
                    </CustomerRecordColumnInfo>
                )}
            </CustomerRecordCard>

            <div className="flex flex-col gap-4 rounded-xl bg-background-more p-5">
                <h2 className="h4">{t('Products')}</h2>

                {complaint.items.map((complaintItem) => (
                    <ComplaintDetailComplaintItem key={complaintItem.uuid} complaintItem={complaintItem} />
                ))}
            </div>
        </>
    );
};
