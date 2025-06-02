import { ComplaintDetailComplaintItem } from './ComplaintDetailComplaintItem';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { ReactNode } from 'react';
import { isResolutionMoneyReturn } from 'utils/complaints/isResolutionMoneyReturn';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { twMergeCustom } from 'utils/twMerge';

type ComplaintDetailBasicInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailBasicInfo: FC<ComplaintDetailBasicInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    return (
        <>
            <div className="bg-background-more vl:px-6 vl:py-4 flex items-center justify-between gap-4 rounded-md px-4 py-3">
                <div className="vl:gap-8 flex flex-wrap gap-6 gap-y-2">
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
                                'max-w-52 xxs:max-w-64 sm:max-w-fit overflow-x-auto overflow-y-hidden whitespace-nowrap',
                                '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most',
                                '[&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                            )}
                        />
                    )}
                </div>
            </div>
            <div className="bg-background-more rounded-xl p-5">
                {complaint.items.map((complaintItem, index) => (
                    <ComplaintDetailComplaintItem key={index} complaint={complaint} complaintItem={complaintItem} />
                ))}
            </div>
        </>
    );
};

type ComplaintItemColumnInfoProps = {
    title: string;
    value: ReactNode;
    valueClassName?: string;
    wrapperClassName?: string;
    tid?: string;
};

export const ComplaintItemColumnInfo: FC<ComplaintItemColumnInfoProps> = ({
    title,
    value,
    valueClassName,
    wrapperClassName,
    tid,
}) => {
    return (
        <div className={twMergeCustom('flex flex-col gap-1', wrapperClassName)}>
            <span className="text-sm">{title}</span>
            <span className={twMergeCustom('font-bold', valueClassName)} data-tid={tid}>
                {value}
            </span>
        </div>
    );
};
