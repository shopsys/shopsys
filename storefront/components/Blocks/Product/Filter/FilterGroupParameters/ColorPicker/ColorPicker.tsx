import CheckboxColor from 'components/Forms/CheckboxColor';
import { FC } from 'react';
import { Controller } from 'react-hook-form';
import { ParametersColorValuesType } from 'types/productFilter';

type ColorPickerProps = {
    parameterParentIndex: number;
    dataItem: ParametersColorValuesType;
    valueIndex: number;
    isDisabled: boolean;
};

const TEST_IDENTIFIER = (valueIndex: number) => 'blocks-product-filter-filtergroupparameters-colorpicker-' + valueIndex;

const ColorPicker: FC<ColorPickerProps> = ({ parameterParentIndex, dataItem, valueIndex: index, isDisabled }) => {
    return (
        <Controller
            name={`parameters.${parameterParentIndex}.values.${index}.checked`}
            render={({ field }) => (
                <>
                    <CheckboxColor
                        name={field.name}
                        id={field.name}
                        disabled={isDisabled}
                        bgColor={dataItem.rgbHex ?? undefined}
                        fieldRef={field}
                        data-testid={TEST_IDENTIFIER(index)}
                        label={dataItem.text}
                    />
                </>
            )}
        />
    );
};

export default ColorPicker;
