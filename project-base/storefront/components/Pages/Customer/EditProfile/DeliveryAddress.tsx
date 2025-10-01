import { AddressList } from 'components/Blocks/AddressList/AddressList';
import { FormBlockWrapper, FormHeading } from 'components/Forms/Form/Form';
import { DeliveryAddressType } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';

type DeliveryAddressProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

export const DeliveryAddress: FC<DeliveryAddressProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const { t } = useTranslation();

    return (
        <FormBlockWrapper>
            <FormHeading>{t('Delivery address')}</FormHeading>

            <AddressList defaultDeliveryAddress={defaultDeliveryAddress} deliveryAddresses={deliveryAddresses} />
        </FormBlockWrapper>
    );
};
