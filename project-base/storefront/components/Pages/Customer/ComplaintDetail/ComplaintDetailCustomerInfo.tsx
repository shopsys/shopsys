import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BoxPackageHandIcon } from 'components/Basic/Icon/BoxPackageHandIcon';
import { UserProfileCardsIcon } from 'components/Basic/Icon/UserProfileCardsIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';

type ComplaintDetailCustomerInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailCustomerInfo: FC<ComplaintDetailCustomerInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();

    return (
        <div className="flex flex-col flex-wrap gap-2.5 rounded-xl bg-backgroundMore p-5 lg:flex-row xl:flex-nowrap">
            <InformationCard heading={t('Contact information')} icon={<UserProfileCardsIcon className="size-8" />}>
                <span>
                    {complaint.deliveryFirstName} {complaint.deliveryLastName}
                </span>

                <ExtendedNextLink
                    href={`mailto:${complaint.email}`}
                    className={twJoin(
                        'hover:text-greyDark text-sm underline hover:no-underline',
                        'overflow-x-auto whitespace-nowrap text-text',
                        '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-backgroundMost [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                    )}
                >
                    {complaint.email}
                </ExtendedNextLink>

                <span>{complaint.deliveryTelephone}</span>
            </InformationCard>

            <InformationCard heading={t('Delivery address')} icon={<BoxPackageHandIcon className="size-8" />}>
                <span>{complaint.deliveryCompanyName && `${complaint.deliveryCompanyName}, `}</span>

                <span>{complaint.deliveryStreet}</span>

                <span>
                    {complaint.deliveryCity}, {complaint.deliveryPostcode}
                </span>

                <span>{complaint.deliveryCountry.name}</span>
            </InformationCard>
        </div>
    );
};
