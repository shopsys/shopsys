import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';

type StoreContactItemProps = {
    email: string | null;
    phone: string | null;
};

export const StoreContact: FC<StoreContactItemProps> = ({ email, phone }) => {
    return (
        <div className="inline-flex flex-col gap-2">
            {email && (
                <a
                    className="inline-flex items-center text-sm font-semibold text-text no-underline"
                    href={'mailto:' + email}
                >
                    <MailIcon className="size-5" />
                    &nbsp;{email}
                </a>
            )}
            {phone && (
                <a
                    className="inline-flex items-center text-sm font-semibold text-text no-underline"
                    href={'tel:' + phone}
                >
                    <PhoneIcon className="size-5" />
                    &nbsp;{phone}
                </a>
            )}
        </div>
    );
};
