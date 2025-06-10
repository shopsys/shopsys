import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { removeSpaces } from 'utils/removeSpaces';

type StoreContactItemProps = {
    email: string | null;
    phone: string | null;
};

export const StoreContact: FC<StoreContactItemProps> = ({ email, phone }) => {
    const cleanPhone = removeSpaces(phone ?? '');

    return (
        <div className="inline-flex flex-col gap-2">
            {email && (
                <a
                    className="text-text-default inline-flex items-center rounded-md text-sm font-semibold no-underline"
                    href={'mailto:' + email}
                    tabIndex={0}
                >
                    <MailIcon className="size-5" />
                    &nbsp;{email}
                </a>
            )}
            {phone && (
                <a
                    className="text-text-default inline-flex items-center rounded-md text-sm font-semibold no-underline"
                    href={'tel:' + cleanPhone}
                    tabIndex={0}
                >
                    <PhoneIcon className="size-5" />
                    &nbsp;{phone}
                </a>
            )}
        </div>
    );
};
