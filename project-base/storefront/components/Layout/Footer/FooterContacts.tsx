import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
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
                ariaLabel={t('Call us at {{ phone }}. {{ openingHours }}', {
                    ns: 'accessibility',
                    phone,
                    openingHours,
                })}
                href={`tel:${cleanPhone}`}
                icon={<PhoneIcon className="size-6" />}
                subtitle={openingHours}
                title={phone}
            />

            <FooterContact
                ariaLabel={t('Write to us at {{ email }}. {{ emailSubtitle }}', {
                    ns: 'accessibility',
                    email,
                    emailSubtitle,
                })}
                href="mailto:info@shopsys.cz"
                icon={<MailIcon className="size-6" />}
                subtitle={emailSubtitle}
                title={email}
            />
        </div>
    );
};
