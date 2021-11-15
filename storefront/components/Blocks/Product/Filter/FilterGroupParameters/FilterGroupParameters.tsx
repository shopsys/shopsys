import { FC, Fragment, useState } from 'react';
import { FilterFormParametersType, FilterFormType, ParameterItemsType } from 'components/Blocks/Product/Filter/types';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { useFieldArray, useFormContext } from 'react-hook-form';
import Checkbox from 'components/Forms/Checkbox';
import CheckboxColor from 'components/Forms/CheckboxColor';
import { clearArrayFromEmptyValue } from 'utils/Filter/ClearArrayFromEmptyValue';

type FilterGroupParametersProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * parameterParentUuid of parameters
     */
    parameterParentUuid: string;
    /**
     * Changes filter items to its elements
     */
    type: string;
    /**
     * Parameters data of product filter
     */
    data: ParameterItemsType[];
    /**
     * Function for submit form
     */
    onSubmit: (data: FilterFormType) => void;
};

const FilterGroupParameters: FC<FilterGroupParametersProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();
    const control = formProviderMethods.control;
    const { append, remove } = useFieldArray({ control, name: 'parameters' });

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    const onChangeParametersCheckbox = (value: string, parameterParentUuid: string) => {
        const formParametersValues = formProviderMethods.getValues().parameters;
        const findedExistingValue: FilterFormParametersType | undefined = formParametersValues.find(
            (item: FilterFormParametersType) => item.parameter === parameterParentUuid,
        );

        if (findedExistingValue !== undefined) {
            const indexOfParameter = formParametersValues.findIndex(
                (item: FilterFormParametersType) => item.parameter === parameterParentUuid,
            );

            if (findedExistingValue.values.includes(value)) {
                const arrayOfValues = [...findedExistingValue.values];
                const indexOfRemovedValue = arrayOfValues.indexOf(value);
                arrayOfValues.splice(indexOfRemovedValue, 1);
                remove(indexOfParameter);
                append({ parameter: parameterParentUuid, values: [...arrayOfValues] });
                clearArrayFromEmptyValue(formProviderMethods);
            } else {
                const arrayOfValues = [...findedExistingValue.values, ...[value]];
                formProviderMethods.setValue(`parameters.${indexOfParameter}.values`, [...arrayOfValues]);
            }
        } else {
            append({ parameter: parameterParentUuid, values: [value] });
            clearArrayFromEmptyValue(formProviderMethods);
        }

        const updatedFormParametersValues = formProviderMethods.getValues().parameters;
        const parameterWithoutValue = updatedFormParametersValues.find(
            (item: FilterFormParametersType) => item.values === undefined,
        );
        if (parameterWithoutValue !== undefined) {
            const indexOfParameterWithoutValue = updatedFormParametersValues.findIndex(
                (item: FilterFormParametersType) => item.parameter === parameterWithoutValue.parameter,
            );
            remove(indexOfParameterWithoutValue);
        }

        props.onSubmit(formProviderMethods.getValues());
    };

    const checkIfParameterIsActive = (itemUuid: string, parentUuid: string) => {
        const formParametersValues = formProviderMethods.getValues().parameters;
        const findedExistingParent = formParametersValues.find(
            (item: FilterFormParametersType) => item.parameter === parentUuid,
        );
        const findedExistingValue = formParametersValues.find((item: FilterFormParametersType) =>
            item.values.find((itemChild) => itemChild === itemUuid),
        );

        return findedExistingValue !== undefined && findedExistingParent !== undefined && true;
    };

    return (
        <FilterGroupStyled>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {props.type === 'checkbox'
                    ? props.data.map((dataItem: ParameterItemsType) => (
                          <FilterGroupContentItemStyled
                              key={dataItem.uuid}
                              isDisabled={dataItem.count === 0}
                              isActive={checkIfParameterIsActive(dataItem.uuid, props.parameterParentUuid)}
                          >
                              <Checkbox
                                  name={dataItem.uuid}
                                  id={dataItem.uuid + props.parameterParentUuid}
                                  label={dataItem.text}
                                  count={dataItem.count}
                                  checked={checkIfParameterIsActive(dataItem.uuid, props.parameterParentUuid)}
                                  onChange={() => onChangeParametersCheckbox(dataItem.uuid, props.parameterParentUuid)}
                              />
                          </FilterGroupContentItemStyled>
                      ))
                    : props.type === 'colorPicker' && (
                          <FilterGroupColorStyled>
                              {props.data.map((dataItem: ParameterItemsType) => (
                                  <Fragment key={dataItem.uuid}>
                                      <CheckboxColor
                                          name={dataItem.uuid}
                                          id={dataItem.uuid + props.parameterParentUuid}
                                          label={dataItem.text}
                                          isDisabled={dataItem.count === 0}
                                          isActive={checkIfParameterIsActive(dataItem.uuid, props.parameterParentUuid)}
                                          bgColor={dataItem.rgbHex}
                                          checked={checkIfParameterIsActive(dataItem.uuid, props.parameterParentUuid)}
                                          onChange={() =>
                                              onChangeParametersCheckbox(dataItem.uuid, props.parameterParentUuid)
                                          }
                                      />
                                  </Fragment>
                              ))}
                          </FilterGroupColorStyled>
                      )}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupParameters;
