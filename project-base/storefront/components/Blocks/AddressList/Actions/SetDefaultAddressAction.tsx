import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useSetDefaultDeliveryAddressMutation } from 'graphql/requests/customer/mutations/SetDefaultDeliveryAddressMutation.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { DeliveryAddressType } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

type SetDefaultAddressActionProps = {
    address: DeliveryAddressType;
    defaultDeliveryAddress: DeliveryAddressType | undefined;
};

export const SetDefaultAddressAction: FC<SetDefaultAddressActionProps> = ({ address, defaultDeliveryAddress }) => {
    const { t } = useTranslation();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutation();
    const { canManagePersonalData } = useAuthorization();

    const getDeliveryAddressAriaLabel = (addressData: DeliveryAddressType): string => {
        const addressParts = [
            `${addressData.firstName} ${addressData.lastName}`,
            `${addressData.street} ${addressData.city}`,
            addressData.postcode,
            addressData.country.name,
            `${t('telephone:')} ${addressData.telephone}`,
        ];

        return ` ${addressParts.join(', ')}`;
    };

    const getSetDefaultAriaLabel = () => {
        return defaultDeliveryAddress?.uuid === address.uuid
            ? t('This is your default delivery address:', { ns: 'accessibility' }) +
                  getDeliveryAddressAriaLabel(defaultDeliveryAddress)
            : canManagePersonalData
              ? t('By clicking this button you will set this address as your default delivery address:', {
                    ns: 'accessibility',
                }) + getDeliveryAddressAriaLabel(address)
              : undefined;
    };

    const setDefaultDeliveryAddressHandler = async (deliveryAddressUuid: string) => {
        if (!canManagePersonalData) {
            return;
        }

        const setDefaultDeliveryAddressResult = await setDefaultDeliveryAddress({ deliveryAddressUuid });

        if (setDefaultDeliveryAddressResult.error !== undefined) {
            showErrorMessage(
                t('There was an error while setting your delivery address as the default one'),
                GtmMessageOriginType.other,
            );
            return;
        }

        showSuccessMessage(t('Your delivery address has been set as default'));
    };

    const handleSetDefaultDeliveryAddress = (e: React.MouseEvent<HTMLButtonElement>) => {
        e.preventDefault();
        setDefaultDeliveryAddressHandler(address.uuid);
    };

    const handleSetDefaultDeliveryAddressKeyDown = (e: React.KeyboardEvent<HTMLButtonElement>) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            setDefaultDeliveryAddressHandler(address.uuid);
        }
    };

    return (
        <Button
            aria-label={getSetDefaultAriaLabel()}
            className="self-start"
            size="small"
            variant="secondary"
            onClick={handleSetDefaultDeliveryAddress}
            onKeyDown={handleSetDefaultDeliveryAddressKeyDown}
        >
            {t('Set as default address')}
        </Button>
    );
};
