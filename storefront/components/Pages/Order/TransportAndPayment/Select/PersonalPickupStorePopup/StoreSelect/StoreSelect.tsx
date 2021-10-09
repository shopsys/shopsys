import { Control, Controller } from 'react-hook-form';
import { FC } from 'react';
import { ListItemStyled } from 'components/Pages/Order/TransportAndPayment/Select/Select.style';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from 'components/Pages/Order/TransportAndPayment/Select/SelectItemLabel';
import { TransportType } from 'connectors/transports/types';

type StoreSelectProps = {
    personalPickupStoreValue: string | null;
    transport: TransportType;
    control: Control<{
        personalPickupStore: null;
    }>;
};

const StoreSelect: FC<StoreSelectProps> = (props) => {
    return (
        <Controller
            name="personalPickupStore"
            control={props.control}
            render={({ field }) => (
                <ul>
                    {props.transport.stores.map((storeItem) => (
                        <ListItemStyled
                            key={storeItem.uuid}
                            isActive={props.personalPickupStoreValue === storeItem.uuid}
                        >
                            <Radiobutton
                                name={field.name}
                                id={storeItem.uuid}
                                value={storeItem.uuid}
                                fieldRef={field}
                                checked={props.personalPickupStoreValue === storeItem.uuid}
                                label={
                                    <SelectItemLabel name={storeItem.name} storeOpeningHours={storeItem.openingHours} />
                                }
                            />
                        </ListItemStyled>
                    ))}
                </ul>
            )}
        />
    );
};

export default StoreSelect;
