import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { DeliveryAddressType } from 'types/customer';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import { DeleteAddressAction } from './Actions/DeleteAddressAction';
import { EditAddressAction } from './Actions/EditAddressAction';
import { SetDefaultAddressAction } from './Actions/SetDefaultAddressAction';

type AddressCardProps = {
    address: DeliveryAddressType;
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    orderSelectedAddress?: boolean;
    orderSelectAddressHandler?: (addressUuid: string) => void;
    hideActions?: boolean;
};

export const AddressCard: FC<AddressCardProps> = ({
    address,
    defaultDeliveryAddress,
    orderSelectedAddress,
    orderSelectAddressHandler,
    hideActions = false,
}) => {
    const { t } = useTranslation();
    const { canManagePersonalData } = useAuthorization();
    const isDefaultAddress = defaultDeliveryAddress?.uuid === address.uuid;
    const orderSelectionMode = orderSelectAddressHandler !== undefined;
    const addressIndex = address.uuid;

    const handleSelectDeliveryAddress = (e: React.MouseEvent<HTMLDivElement, MouseEvent>) => {
        if (!orderSelectAddressHandler) {
            return;
        }

        e.preventDefault();
        orderSelectAddressHandler(address.uuid);
    };

    const handleSelectDeliveryAddressKeyDown = (e: React.KeyboardEvent<HTMLDivElement>) => {
        if (!orderSelectAddressHandler) {
            return;
        }

        if (e.key === 'Enter') {
            e.preventDefault();
            orderSelectAddressHandler(address.uuid);
        }
    };

    return (
        /* biome-ignore lint/a11y/useSemanticElements: The selectable card can also contain nested action buttons, so the wrapper cannot always be a button element. */
        <div
            data-tid={`${TIDs.blocks_addresslist_addresscard_}${addressIndex}`}
            role="button"
            tabIndex={0}
            aria-label={
                orderSelectionMode
                    ? t('Set this address as the delivery address for the order', { ns: 'accessibility' })
                    : undefined
            }
            className={twMergeCustom(
                'relative flex w-full justify-between rounded-md border-2 p-4 text-left',
                'border-border-less bg-background-default',
                orderSelectedAddress && 'border-border-brand',
                orderSelectionMode && 'cursor-pointer hover:border-link-hovered focus-visible:bg-orange-500',
            )}
            onClick={handleSelectDeliveryAddress}
            onKeyDown={handleSelectDeliveryAddressKeyDown}
        >
            <div className="flex w-full flex-col gap-2">
                {isDefaultAddress && (
                    <Button disabled hasDisabledLook className="self-start" size="small">
                        {t('Default address')}
                    </Button>
                )}

                {!isDefaultAddress && !orderSelectionMode && (
                    <SetDefaultAddressAction address={address} defaultDeliveryAddress={defaultDeliveryAddress} />
                )}

                <div className="flex flex-col">
                    <strong className="mr-1">
                        {address.firstName} {address.lastName}
                    </strong>

                    <span>{address.companyName}</span>

                    <span>
                        {address.street}, {address.city}, {address.postcode}
                    </span>

                    <span>{address.country.name}</span>

                    <span>{address.telephone}</span>
                </div>

                {canManagePersonalData && !hideActions && (
                    <div className="mt-auto flex flex-col gap-2">
                        <EditAddressAction address={address} />

                        <DeleteAddressAction address={address} />
                    </div>
                )}
            </div>
        </div>
    );
};
