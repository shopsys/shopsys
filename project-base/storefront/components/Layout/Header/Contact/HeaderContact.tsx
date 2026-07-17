import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { removeSpaces } from 'utils/removeSpaces';
import { useContacts } from 'utils/useContacts';

export const HeaderContact: FC = () => {
    const { t } = useTranslation();
    const { phone, openingHours } = useContacts();
    const cleanPhone = removeSpaces(phone);

    return (
        <div className="order-2 ml-auto flex">
            <div className="relative flex flex-1 flex-col items-start lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-wrap items-center gap-3 lg:flex-1 xl:justify-center">
                    <a
                        aria-label={t('Call us at {{ phone }}. {{ openingHours }}', {
                            ns: 'accessibility',
                            phone,
                            openingHours,
                        })}
                        className="rounded-md font-bold text-text-inverted no-underline hover:text-text-inverted focus-visible:ring-1"
                        href={`tel:${cleanPhone}`}
                        tid={TIDs.simple_header_contact}
                    >
                        <PhoneIcon className="size-6" />
                        {phone}
                    </a>

                    <p className="hidden text-sm text-text-inverted lg:block"> {openingHours}</p>
                </div>
            </div>
        </div>
    );
};
