import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { BoxPackageHandIcon } from 'components/Basic/Icon/BoxPackageHandIcon';
import { UserProfileCardsIcon } from 'components/Basic/Icon/UserProfileCardsIcon';
import { InformationCard } from 'components/Basic/InformationCard/InformationCard';
import { TypeComplaintDetailFragment } from 'graphql/requests/complaints/fragments/ComplaintDetailFragment.generated';
import { twJoin } from 'tailwind-merge';
import { normalizeTelephone } from 'utils/formaters/normalizeTelephone';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type ComplaintDetailCustomerInfoProps = {
    complaint: TypeComplaintDetailFragment;
};

export const ComplaintDetailCustomerInfo: FC<ComplaintDetailCustomerInfoProps> = ({ complaint }) => {
    const { t } = useTranslation();

    return (
        <div className="grid grid-cols-1 vl:grid-cols-3 gap-2.5 rounded-xl bg-background-more p-5 lg:grid-cols-2">
            <InformationCard heading={t('Contact information')} icon={<UserProfileCardsIcon className="size-8" />}>
                <span>
                    {complaint.deliveryFirstName} {complaint.deliveryLastName}
                </span>

                <ExtendedNextLink
                    href={`mailto:${complaint.email}`}
                    className={twJoin(
                        'overflow-x-auto whitespace-nowrap text-sm text-text-default underline hover:no-underline',
                        '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                    )}
                >
                    {complaint.email}
                </ExtendedNextLink>

                <span>{normalizeTelephone(complaint.deliveryTelephone)}</span>
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
