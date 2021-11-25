import CheckboxColor from 'components/Forms/CheckboxColor';
import { Controller } from 'react-hook-form';
import { FC } from 'react';
import { FilterFormParameterValuesType } from 'components/Blocks/Product/Filter/types';

type ColorPickerProps = {
    parameterParentIndex: number;
    parameterParentUuid: string;
    dataItem: FilterFormParameterValuesType;
    index: number;
    isDisabled: boolean;
};

const ColorPicker: FC<ColorPickerProps> = (props) => {
    return (
        <Controller
            name={`parameters.${props.parameterParentIndex}.values.${props.index}.checked`}
            render={({ field }) => (
                <>
                    <CheckboxColor
                        name={field.name}
                        id={field.name}
                        isDisabled={props.isDisabled}
                        isActive={field.value}
                        bgColor={props.dataItem.rgbHex as string}
                        fieldRef={field}
                    />
                </>
            )}
        />
    );
};

export default ColorPicker;
