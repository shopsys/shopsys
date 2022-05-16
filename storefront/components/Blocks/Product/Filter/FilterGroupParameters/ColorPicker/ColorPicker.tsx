import CheckboxColor from 'components/Forms/CheckboxColor';
import { FC } from 'react';
import { Controller } from 'react-hook-form';
import { FilterFormParameterValuesType } from 'types/productFilter';

type ColorPickerProps = {
    parameterParentIndex: number;
    parameterParentUuid: string;
    dataItem: FilterFormParameterValuesType;
    index: number;
    isDisabled: boolean;
};

const ColorPicker: FC<ColorPickerProps> = (props) => {
    const testIdentifier = 'blocks-product-filter-filtergroupparameters-colorpicker-' + props.index;

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
                        data-testid={testIdentifier}
                    />
                </>
            )}
        />
    );
};

export default ColorPicker;
