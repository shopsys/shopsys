import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { removeSpaces } from 'utils/removeSpaces';

type StoreContactItemProps = {
    email: string | null;
    phone: string | null;
};

export const StoreContact: FC<StoreContactItemProps> = ({ email, phone }) => {
    const cleanPhone = removeSpaces(phone ?? '');
    const { t } = useTranslation();

    return (
        <div className="inline-flex flex-col gap-2">
            {email && (
                <a
                    aria-label={t('Mail to {{email}}', { ns: 'accessibility', email })}
                    className="inline-flex items-center rounded-md font-semibold text-sm text-text-default no-underline"
                    href={`mailto:${email}`}
                    tabIndex={0}
                >
                    <MailIcon className="size-5" />
                    &nbsp;{email}
                </a>
            )}
            {phone && (
                <a
                    aria-label={t('Call to {{phone}}', { ns: 'accessibility', phone })}
                    className="inline-flex items-center rounded-md font-semibold text-sm text-text-default no-underline"
                    href={`tel:${cleanPhone}`}
                    tabIndex={0}
                >
                    <PhoneIcon className="size-5" />
                    &nbsp;{phone}
                </a>
            )}
        </div>
    );
};
