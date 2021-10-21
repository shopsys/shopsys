import { FC, useState } from 'react';
import {
    ProductFilterGroupArrowStyled,
    ProductFilterGroupCheckboxStyled,
    ProductFilterGroupColorStyled,
    ProductFilterGroupContentStyled,
    ProductFilterGroupStyled,
    ProductFilterGroupTitleStyled,
} from './ProductFilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import CheckboxColor from 'components/Forms/CheckboxColor';
import { Controller } from 'react-hook-form';
import RangeSlider from 'components/Basic/RangeSlider';

type ProductFilterGroupProps = {
    /**
     * Changes filter items to its elements
     */
    type: 'checkbox' | 'color' | 'price';
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen?: boolean;
};

const ProductFilterGroup: FC<ProductFilterGroupProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <ProductFilterGroupStyled>
            <ProductFilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <ProductFilterGroupArrowStyled icon="Arrow" isOpen={isGroupOpen} />
            </ProductFilterGroupTitleStyled>
            <ProductFilterGroupContentStyled isOpen={isGroupOpen}>
                {renderItems(props.type)}
            </ProductFilterGroupContentStyled>
        </ProductFilterGroupStyled>
    );
};

/**
 * TODO PRG: join real data from API - Product filter items
 */
const renderItems = (type: 'checkbox' | 'color' | 'price') => {
    switch (type) {
        case 'checkbox':
            return (
                <>
                    <ProductFilterGroupCheckboxStyled>
                        <Controller
                            name="checkboxWithLink"
                            render={({ field }) => (
                                <Checkbox
                                    id="group1item1"
                                    name={field.name}
                                    label="this is a link"
                                    hasError={false}
                                    isTouched={false}
                                />
                            )}
                        />
                    </ProductFilterGroupCheckboxStyled>
                    <ProductFilterGroupCheckboxStyled>
                        <Controller
                            name="checkboxWithLink"
                            render={({ field }) => (
                                <Checkbox
                                    id="group1item2"
                                    name={field.name}
                                    label="this is a link"
                                    hasError={false}
                                    isTouched={false}
                                />
                            )}
                        />
                    </ProductFilterGroupCheckboxStyled>
                    <ProductFilterGroupCheckboxStyled>
                        <Controller
                            name="checkboxWithLink"
                            render={({ field }) => (
                                <Checkbox
                                    id="group1item3"
                                    name={field.name}
                                    label="this is a link"
                                    hasError={false}
                                    isTouched={false}
                                />
                            )}
                        />
                    </ProductFilterGroupCheckboxStyled>
                </>
            );
        case 'color':
            return (
                <ProductFilterGroupColorStyled>
                    <Controller
                        name="groupcolor1"
                        render={({ field }) => (
                            <CheckboxColor
                                bgColor="red"
                                isLightColor={false}
                                id="groupcolor1"
                                name={field.name}
                                label="this is a link"
                            />
                        )}
                    />
                    <Controller
                        name="groupcolor1"
                        render={({ field }) => (
                            <CheckboxColor
                                bgColor="yellow"
                                isLightColor={true}
                                id="groupcolor2"
                                name={field.name}
                                label="this is a link"
                            />
                        )}
                    />
                    <Controller
                        name="groupcolor1"
                        render={({ field }) => (
                            <CheckboxColor
                                bgColor="black"
                                id="groupcolor3"
                                name={field.name}
                                label="this is a link"
                                isLightColor={false}
                            />
                        )}
                    />
                </ProductFilterGroupColorStyled>
            );
        case 'price':
            return (
                <>
                    <RangeSlider min={0} max={1000} delay={300} />
                </>
            );
    }
    throw new Error('Wrong type provided for Product filter group.' + type);
};

/* @component */
export default ProductFilterGroup;
