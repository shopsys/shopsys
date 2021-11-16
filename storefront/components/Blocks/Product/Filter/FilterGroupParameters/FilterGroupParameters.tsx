import { Controller, useFieldArray, useFormContext } from 'react-hook-form';
import { FC, useState } from 'react';
import { FilterFormType, ParametersType } from 'components/Blocks/Product/Filter/types';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import CheckboxColor from 'components/Forms/CheckboxColor';

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
     * parameterParentIndex of parameters
     */
    parameterParentIndex: number;
    /**
     * Changes filter items to its elements
     */
    type: string;
    /**
     * Parameters data of product filter
     */
    data?: ParametersType;
};

const FilterGroupParameters: FC<FilterGroupParametersProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();
    const { fields } = useFieldArray({
        control: formProviderMethods.control,
        name: `parameters.${props.parameterParentIndex}.values`,
    });
    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {props.type === 'checkbox'
                    ? fields.map((dataItem, index) => (
                          <Controller
                              key={dataItem.id}
                              name={`parameters.${props.parameterParentIndex}.values.${index}.checked`}
                              render={({ field }) => (
                                  <FilterGroupContentItemStyled
                                      key={dataItem.uuid}
                                      isDisabled={props.data?.values?.[index]?.count === 0}
                                      isActive={field.value}
                                  >
                                      <Checkbox
                                          name={field.name}
                                          id={field.name}
                                          label={dataItem.text}
                                          fieldRef={field}
                                          count={props.data?.values?.[index]?.count}
                                      />
                                  </FilterGroupContentItemStyled>
                              )}
                          />
                      ))
                    : props.type === 'colorPicker' && (
                          <FilterGroupColorStyled>
                              {fields.map((dataItem, index) => (
                                  <Fragment key={dataItem.id}>
                                      <Controller
                                          name={`parameters.${props.parameterParentIndex}.values.${index}.checked`}
                                          render={({ field }) => (
                                              <CheckboxColor
                                                  name={dataItem.uuid}
                                                  id={dataItem.uuid + props.parameterParentUuid}
                                                  isDisabled={
                                                      props.data?.values?.[index] !== undefined
                                                          ? props.data.values[index].count === 0
                                                          : dataItem.count === 0
                                                  }
                                                  isActive={field.value}
                                                  bgColor={dataItem.rgbHex as string}
                                                  fieldRef={field}
                                              />
                                          )}
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
