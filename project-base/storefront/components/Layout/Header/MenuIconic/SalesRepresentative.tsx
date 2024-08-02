import { MailIcon } from 'components/Basic/Icon/MailIcon';
import { PhoneIcon } from 'components/Basic/Icon/PhoneIcon';
import { Image } from 'components/Basic/Image/Image';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeSalesRepresentative } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';

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
        <div className="flex items-start gap-4 w-full pt-4">
            {salesRepresentative.image && (
                <Image
                    alt={t('Need advice?')}
                    className="w-12 h-12 rounded-full object-cover"
                    height={100}
                    src={salesRepresentative.image.url}
                    width={100}
                />
            )}
            <div className="font-medium dark:text-white w-full">
                {fullName && (
                    <>
                        <p className="text-lg font-semibold font-secondary leading-7">{fullName}</p>
                        <p className="text-xs font-semibold font-secondary text-textSubtle uppercase tracking-wider">
                            {t('Your sales representative')}
                        </p>
                    </>
                )}
                {telephone && (
                    <div className="flex gap-2 items-center my-2">
                        <PhoneIcon className="w-6 h-6 p-0.5 flex-shrink-0" />
                        <a className="no-underline text-sm text-text" href={`tel:${telephone}`}>
                            {formatPhoneNumber(telephone)}
                        </a>
                    </div>
                )}
                {email && (
                    <div className="flex gap-2 items-center mt-1 w-full max-w-80 lg:max-w-full overflow-auto">
                        <MailIcon className="w-6 h-6 flex-shrink-0" />
                        <a className="max-w-44 lg:max-w-96 no-underline text-sm text-text" href={`mailto:${email}`}>
                            {email}
                        </a>
                    </div>
                )}
            </div>
        </div>
    );
};

const getFullName = (firstName?: string | null, lastName?: string | null): string | null | undefined => {
    if (!firstName || !lastName) {
        return firstName ?? lastName;
    }
    return `${firstName} ${lastName}`;
};

const formatPhoneNumber = (phoneNumber: string): string | null | undefined => {
    if (phoneNumber && phoneNumber.length === 9) {
        return phoneNumber.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
    }
    return phoneNumber ? phoneNumber : undefined;
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
