import { MailSecondaryIcon } from 'components/Basic/Icon/MailSecondaryIcon';
import { PhoneSecondaryIcon } from 'components/Basic/Icon/PhoneSecondaryIcon';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { removeSpaces } from 'utils/removeSpaces';
import { useContacts } from 'utils/useContacts';
import { FooterContact } from './FooterContact';

export const FooterContacts: FC = () => {
    const { t } = useTranslation();
    const { phone, openingHours, email, emailSubtitle } = useContacts();
    const cleanPhone = removeSpaces(phone);

    return (
        <div className="flex flex-col gap-4">
            <FooterContact
                ariaLabel={t('Call us', { ns: 'accessibility' })}
                href={`tel:${cleanPhone}`}
                icon={<PhoneSecondaryIcon className="size-6" />}
                subtitle={openingHours}
                title={phone}
            />

            <FooterContact
                ariaLabel={t('Write to us', { ns: 'accessibility' })}
                href="mailto:info@shopsys.cz"
                icon={<MailSecondaryIcon className="size-6" />}
                subtitle={emailSubtitle}
                title={email}
            />
        </div>
    );
};
