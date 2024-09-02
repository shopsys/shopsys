import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { UserIcon } from 'components/Basic/Icon/UserIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';

type ComplaintDetailCustomerInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailCustomerInfo: FC<ComplaintDetailCustomerInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();

    return (
        <div className="flex w-full flex-col vl:flex-row vl:flex-wrap xl:flex-nowrap gap-6">
            <InformationCard
                heading={t('Contact information')}
                icon={<UserIcon className="[&>path]:stroke-1" isFull={false} />}
            >
                <span>
                    {complaint.deliveryFirstName} {complaint.deliveryLastName}
                </span>

                <span>{complaint.deliveryTelephone}</span>
            </InformationCard>

            <InformationCard heading={t('Delivery address')} icon={<MailIcon />}>
                <span>{complaint.deliveryCompanyName && `${complaint.deliveryCompanyName}, `} </span>

                <span>
                    {complaint.deliveryStreet}, {complaint.deliveryCity}, {complaint.deliveryPostcode}
                </span>

                <span>{complaint.deliveryCountry.name}</span>
            </InformationCard>
        </div>
    );
};
