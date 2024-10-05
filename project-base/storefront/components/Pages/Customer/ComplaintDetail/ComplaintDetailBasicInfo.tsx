import { ComplaintDetailComplaintItem } from './ComplaintDetailComplaintItem';
import { TIDs } from 'cypress/tids';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { ReactNode } from 'react';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { twMergeCustom } from 'utils/twMerge';

type ComplaintDetailBasicInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailBasicInfo: FC<ComplaintDetailBasicInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    return (
        <div className="my-6 flex flex-col gap-4 bg-background vl:mb-8">
            <div className="flex items-center justify-between gap-4 rounded-md bg-backgroundMore px-4 py-3 vl:px-6 vl:py-4">
                <div className="flex  flex-wrap gap-6 gap-y-2 vl:gap-8">
                    <ComplaintItemColumnInfo
                        tid={TIDs.complaint_detail_number}
                        title={t('Complaint number')}
                        value={complaint.number}
                    />
                    <ComplaintItemColumnInfo
                        tid={TIDs.complaint_detail_creation_date}
                        title={t('Creation date')}
                        value={formatDate(complaint.createdAt, 'DD. MM. YYYY')}
                    />
                    <ComplaintItemColumnInfo title={t('Status')} value={complaint.status} />
                </div>
            </div>
            <div className="rounded-md border-[5px] border-borderLess bg-background p-7">
                {complaint.items.map((complaintItem, index) => (
                    <ComplaintDetailComplaintItem key={index} complaintItem={complaintItem} />
                ))}
            </div>
        </div>
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
        <div className={twMergeCustom('flex items-end gap-4', wrapperClassName)}>
            <div className="flex flex-col gap-1">
                <span className="text-sm">{title}</span>
                <span className={twMergeCustom('font-bold leading-none', valueClassName)} tid={tid}>
                    {value}
                </span>
            </div>
        </div>
    );
};
