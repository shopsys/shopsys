import {
    ButtonBackIconStyled,
    ButtonNextIconStyled,
    ButtonWrapperStyled,
    ListItemDeleteStyled,
    ListItemIconStyled,
    ListItemStyled,
    ListPopupInStyled,
    ListPopupStyled,
    ListStyled,
} from './AddressList.style';
import { FC, useState } from 'react';
import { showErrorMessage, showSuccessMessage } from 'components/Helpers/Toasts';
import Button from 'components/Forms/Button';
import { DeliveryAddressType } from 'types/customer';
import Popup from 'components/Layout/Popup';
import { useDeleteDeliveryAddressMutationApi } from 'graphql/generated';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type AddressListProps = {
    defaultDeliveryAddress: DeliveryAddressType | undefined;
    deliveryAddresses: DeliveryAddressType[];
};

const AddressList: FC<AddressListProps> = (props) => {
    const testIdentifier = 'list-addresses';
    const [addressToBeDeleted, setAddressToBeDeleted] = useState<string | undefined>(undefined);
    const [, deleteDeliveryAddress] = useDeleteDeliveryAddressMutationApi();
    const t = useTypedTranslationFunction();

    const deleteItemHandler = async (deliveryAddressUuid: string | undefined) => {
        if (deliveryAddressUuid === undefined) {
            return;
        }

        setAddressToBeDeleted(undefined);
        const deleteDeliveryAddressResult = await deleteDeliveryAddress({ deliveryAddressUuid });

        if (deleteDeliveryAddressResult.error !== undefined) {
            showErrorMessage(t('There was an error while deleting your delivery address'));
            return;
        }

        showSuccessMessage(t('Your delivery address has been deleted'));
    };

    return (
        <>
            <ListStyled>
                {props.deliveryAddresses.map((address, index) => (
                    <>
                        <ListItemStyled
                            key={address.uuid}
                            isActive={props.defaultDeliveryAddress?.uuid === address.uuid}
                            data-testid={testIdentifier + '-item-' + index}
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
                                        <ListItemIconStyled iconType="icon" icon="Phone" />
                                        {address.telephone}
                                    </>
                                )}
                            </div>

                            <ListItemDeleteStyled
                                icon="Remove"
                                iconType="icon"
                                onClick={() => setAddressToBeDeleted(address.uuid)}
                            />
                        </ListItemStyled>
                    </>
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
                            <ButtonBackIconStyled iconType="icon" icon="Arrow" />
                            {t('No')}
                        </Button>
                        <Button type="button" onClick={() => deleteItemHandler(addressToBeDeleted)}>
                            {t('Yes')}
                            <ButtonNextIconStyled iconType="icon" icon="Arrow" />
                        </Button>
                    </ButtonWrapperStyled>
                </ListPopupInStyled>
            </Popup>
        </>
    );
};

export default AddressList;
