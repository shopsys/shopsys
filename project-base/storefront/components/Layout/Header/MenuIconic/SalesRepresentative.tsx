import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { Image } from 'components/Basic/Image/Image';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeSalesRepresentative } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import { formatPhoneNumber } from 'utils/formaters/formatPhoneNumber';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export const SalesRepresentative: FC = () => {
    const { t } = useTranslation();
    const currentCustomerData = useCurrentCustomerData();
    const salesRepresentative = currentCustomerData?.salesRepresentative;
    if (!salesRepresentative) {
        return null;
    }

    const { telephone, email } = salesRepresentative;
    const fullName = getFullName(salesRepresentative.firstName, salesRepresentative.lastName);

    if (!getShowSalesRepresentative(salesRepresentative)) {
        return null;
    }

    return (
        <div className="flex flex-col gap-1">
            <div className="flex items-center gap-2">
                {salesRepresentative.image && (
                    <Image
                        alt={t('Need advice?')}
                        className="size-12 rounded-full object-cover"
                        height={100}
                        src={salesRepresentative.image.url}
                        width={100}
                    />
                )}
                <div className="flex flex-col">
                    {fullName && <span className="h5">{fullName}</span>}
                    <span className="h6 text-text-less">{t('Your sales representative')}</span>
                </div>
            </div>

            {telephone && (
                <div className="mt-2 flex items-center gap-2">
                    <PhoneIcon className="size-6" />
                    <a
                        aria-label={t('Call sales representative', { ns: 'accessibility' })}
                        className="rounded-md font-semibold text-sm text-text-default no-underline"
                        href={`tel:${telephone}`}
                        tabIndex={0}
                    >
                        {formatPhoneNumber(telephone)}
                    </a>
                </div>
            )}

            {email && (
                <div className="flex items-center gap-2">
                    <MailIcon className="size-6" />
                    <a
                        aria-label={t('Send email to sales representative', { ns: 'accessibility' })}
                        href={`mailto:${email}`}
                        tabIndex={0}
                        className={twJoin(
                            'max-w-64 overflow-x-auto whitespace-nowrap rounded-md font-semibold text-sm text-text-default no-underline',
                            '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-background-most [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
                        )}
                    >
                        {email}
                    </a>
                </div>
            )}
        </div>
    );
};

const getFullName = (firstName?: string | null, lastName?: string | null): string | null | undefined => {
    if (!firstName || !lastName) {
        return firstName ?? lastName;
    }
    return `${firstName} ${lastName}`;
};

const getShowSalesRepresentative = (salesRepresentative: TypeSalesRepresentative | null | undefined): boolean => {
    return (
        !!salesRepresentative &&
        !!(
            salesRepresentative.firstName ||
            salesRepresentative.lastName ||
            salesRepresentative.email ||
            salesRepresentative.telephone
        )
    );
};
