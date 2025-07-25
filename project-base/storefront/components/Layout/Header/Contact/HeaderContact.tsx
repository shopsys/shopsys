import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { removeSpaces } from 'utils/removeSpaces';
import { useContacts } from 'utils/useContacts';

export const HeaderContact: FC = () => {
    const { t } = useTranslation();
    const { phone, openingHours } = useContacts();
    const cleanPhone = removeSpaces(phone);

    return (
        <div className="order-2 ml-auto flex">
            <div className="relative flex flex-1 flex-col items-start py-4 pr-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex flex-wrap items-center gap-3 lg:flex-1 xl:justify-center">
                    <PhoneIcon className="text-text-inverted w-5" />

                    <a
                        aria-label={t('Call us')}
                        className="text-text-inverted hover:text-text-inverted rounded-md font-bold no-underline focus-visible:ring-1"
                        href={'tel:' + cleanPhone}
                        tid={TIDs.simple_header_contact}
                    >
                        {phone}
                    </a>

                    <p className="text-text-inverted hidden text-sm lg:block"> {openingHours}</p>
                </div>
            </div>
        </div>
    );
};
