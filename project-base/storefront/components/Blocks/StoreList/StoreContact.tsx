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
                    className="text-text-default inline-flex items-center rounded-md text-sm font-semibold no-underline focus-visible:ring-2"
                    href={'mailto:' + email}
                >
                    <MailIcon className="size-5" />
                    &nbsp;{email}
                </a>
            )}
            {phone && (
                <a
                    className="text-text-default inline-flex items-center rounded-md text-sm font-semibold no-underline focus-visible:ring-2"
                    href={'tel:' + phone}
                >
                    <PhoneIcon className="size-5" />
                    &nbsp;{phone}
                </a>
            )}
        </div>
    );
};
