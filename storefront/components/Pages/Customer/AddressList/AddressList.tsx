import {
    ButtonWrapperStyled,
    ListItemStyled,
    ListPopupInStyled,
    ListPopupStyled,
    ListStyled,
} from './AddressList.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Button } from 'components/Forms/Button/Button';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import { Popup } from 'components/Layout/Popup/Popup';
import { useDeleteDeliveryAddressMutationApi, useSetDefaultDeliveryAddressMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useState } from 'react';
import { DeliveryAddressType } from 'types/customer';

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

const TEST_IDENTIFIER = 'list-addresses';

export const AddressList: FC<AddressListProps> = ({ defaultDeliveryAddress, deliveryAddresses }) => {
    const [addressToBeDeleted, setAddressToBeDeleted] = useState<string | undefined>(undefined);
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutationApi();
    const [, setDefaultDeliveryAddress] = useSetDefaultDeliveryAddressMutationApi();
    const t = useTypedTranslationFunction();

    const deleteItemHandler = async (deliveryAddressUuid: string | undefined) => {
        if (deliveryAddressUuid === undefined) {
            return;
        }

        setAddressToBeDeleted(undefined);
        const deleteDeliveryAddressResult = await deleteDeliveryAddress({ deliveryAddressUuid });

        if (deleteDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting your delivery address'), 'other');
            return;
        }

        showSuccessMessage(t('Your delivery address has been deleted'));
    };

    const setDefaultItemHandler = async (deliveryAddressUuid: string) => {
        const result = await setDefaultDeliveryAddress({ deliveryAddressUuid });

        if (result.error !== undefined) {
            showErrorMessage(t('There was an error while setting your delivery address as the default one'), 'other');
            return;
        }

        showSuccessMessage(t('Your delivery address has been set as default'));
    };

    return (
        <>
            <ListStyled>
                {deliveryAddresses.map((address, index) => (
                    <ListItemStyled
                        key={address.uuid}
                        isActive={defaultDeliveryAddress?.uuid === address.uuid}
                        data-testid={TEST_IDENTIFIER + '-item-' + index}
                        onClick={() => setDefaultItemHandler(address.uuid)}
                    >
                        <div>
                            <strong>
                                {address.firstName} {address.lastName}
                            </strong>
                            {address.companyName}
                            <br />
                            {address.street}, {address.city}, {address.postcode}
                            <br />
                            {address.country}
                            <br />
                            {address.telephone && (
                                <>
                                    <Icon iconType="icon" icon="Phone" className="relative top-[2px] mr-1" />
                                    {address.telephone}
                                </>
                            )}
                        </div>

                        <Icon
                            icon="Remove"
                            iconType="icon"
                            onClick={() => setAddressToBeDeleted(address.uuid)}
                            width={12}
                            height={12}
                            className="absolute right-5 top-5 cursor-pointer text-greyLight hover:text-red"
                        />
                    </ListItemStyled>
                ))}
            </ListStyled>
            <Popup
                isVisible={addressToBeDeleted !== undefined}
                onCloseCallback={() => setAddressToBeDeleted(undefined)}
                wrapperComponent={ListPopupStyled}
            >
                <ListPopupInStyled>
                    {t('Do you really want to delete this delivery address?')}
                    <ButtonWrapperStyled>
                        <Button type="button" onClick={() => setAddressToBeDeleted(undefined)}>
                            <Icon iconType="icon" icon="Arrow" className="relative mr-4 rotate-90 text-white" />
                            {t('No')}
                        </Button>
                        <Button type="button" onClick={() => deleteItemHandler(addressToBeDeleted)}>
                            {t('Yes')}
                            <Icon iconType="icon" icon="Arrow" className="relative ml-4 -rotate-90" />
                        </Button>
                    </ButtonWrapperStyled>
                </ListPopupInStyled>
            </Popup>
        </>
    );
};
