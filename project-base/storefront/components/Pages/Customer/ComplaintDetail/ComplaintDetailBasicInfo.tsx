import { ComplaintItemColumnInfo } from 'components/Pages/Customer/Complaints/ComplaintItemColumnInfo';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { ComplaintDetailComplaintItem } from './ComplaintDetailComplaintItem';

type ComplaintDetailBasicInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailBasicInfo: FC<ComplaintDetailBasicInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    return (
        <>
            <div className="flex items-center justify-between gap-4 rounded-md bg-background-more px-4 vl:px-6 py-3 vl:py-4">
                <div className="flex flex-wrap gap-6 vl:gap-8 gap-y-2">
                    <ComplaintItemColumnInfo
                        tid={TIDs.complaint_detail_number}
                        title={t('Complaint number')}
                        value={complaint.number}
                    />
                    <ComplaintItemColumnInfo
                        tid={TIDs.complaint_detail_creation_date}
                        title={t('Creation date')}
                        value={formatDate(complaint.createdAt)}
                    />
                    <ComplaintItemColumnInfo title={t('Status')} value={complaint.status} />
                    <ComplaintItemColumnInfo title={t('Resolution')} value={complaint.resolution.name} />
                    {isResolutionMoneyReturn(complaint.resolution) && (
                        <ComplaintItemColumnInfo
                            tid={TIDs.complaint_detail_bank_account_number}
                            title={t('Bank account number')}
                            value={complaint.bankAccountNumber}
                            valueClassName={twMergeCustom(
                                'max-w-52 xxs:max-w-64 overflow-x-auto overflow-y-hidden whitespace-nowrap sm:max-w-fit',
                                '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most',
                                '[&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                            )}
                        />
                    )}
                </div>
            </div>
            <div className="flex flex-col gap-2 rounded-xl bg-background-more p-5">
                {complaint.items.map((complaintItem) => (
                    <ComplaintDetailComplaintItem
                        key={complaintItem.uuid}
                        complaint={complaint}
                        complaintItem={complaintItem}
                    />
                ))}
            </div>
        </>
    );
};
