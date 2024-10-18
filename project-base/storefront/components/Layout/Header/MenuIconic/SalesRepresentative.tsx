import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { Image } from 'components/Basic/Image/Image';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeSalesRepresentative } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { twJoin } from 'tailwind-merge';
import { formatPhoneNumber } from 'utils/formaters/formatPhoneNumber';

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
        <div className="flex flex-col gap-1 pt-3">
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
                <div>
                    {fullName && <h5>{fullName}</h5>}
                    <h6 className="text-textSubtle">{t('Your sales representative')}</h6>
                </div>
            </div>
            {telephone && (
                <div className="mt-2 flex items-center gap-2">
                    <PhoneIcon className="size-6" />
                    <a className="text-sm font-semibold text-text no-underline" href={`tel:${telephone}`}>
                        {formatPhoneNumber(telephone)}
                    </a>
                </div>
            )}
            {email && (
                <div className="flex items-center gap-2">
                    <MailIcon className="size-6" />
                    <a
                        href={`mailto:${email}`}
                        className={twJoin(
                            'max-w-64 overflow-x-auto whitespace-nowrap text-sm font-semibold text-text no-underline',
                            '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-backgroundMost [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:h-1',
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
