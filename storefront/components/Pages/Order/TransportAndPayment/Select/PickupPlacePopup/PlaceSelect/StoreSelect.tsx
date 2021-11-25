import { Control, Controller } from 'react-hook-form';
import { FC } from 'react';
import { ListItemStyled } from 'components/Pages/Order/TransportAndPayment/Select/Select.style';
import Radiobutton from 'components/Forms/Radiobutton';
import SelectItemLabel from 'components/Pages/Order/TransportAndPayment/Select/SelectItemLabel';
import { TransportType } from 'connectors/transports/types';

type StoreSelectProps = {
    pickupPlaceValue: string | null;
    transport: TransportType;
    control: Control<{
        pickupPlace: null;
    }>;
};

const StoreSelect: FC<StoreSelectProps> = (props) => {
    return (
        <Controller
            name="pickupPlace"
            control={props.control}
            render={({ field }) => (
                <ul>
                    {props.transport.stores.map((pickupPlaceItem) => {
                        return (
                            <ListItemStyled
                                key={pickupPlaceItem.identifier}
                                isActive={props.pickupPlaceValue === pickupPlaceItem.identifier}
                            >
                                <Radiobutton
                                    name={field.name}
                                    id={pickupPlaceItem.identifier}
                                    value={pickupPlaceItem.identifier}
                                    fieldRef={field}
                                    checked={props.pickupPlaceValue === pickupPlaceItem.identifier}
                                    label={
                                        <SelectItemLabel
                                            name={pickupPlaceItem.name}
                                            pickupPlaceDetail={pickupPlaceItem}
                                        />
                                    }
                                />
                            </ListItemStyled>
                        );
                    })}
                </ul>
            )}
        />
    );
};

export default StoreSelect;
