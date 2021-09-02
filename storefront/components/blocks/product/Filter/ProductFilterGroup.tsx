import {
    ProductFilterGroupContentStyled,
    ProductFilterGroupStyled,
    ProductFilterGroupColorStyled,
    ProductFilterGroupTitleStyled,
} from './ProductFilterGroup.style';
import { ReactElement, useState } from 'react';
import ShopsysCheckbox from 'components/forms/ShopsysCheckbox';
import ShopsysCheckboxColor from 'components/forms/ShopsysCheckboxColor';
import ShopsysIcon from '../../../basic/ShopsysIcon';
import ShopsysRangeSlider from '../../../basic/ShopsysRangeSlider';

const ProductFilterGroup = (props): ReactElement => {
    const [isGroupOpen, setIsGroupOpen] = useState(true);

    function handleGroupClick() {
        setIsGroupOpen(!isGroupOpen);
    }

    const RenderItems = (type: 'checkbox' | 'color' | 'price') => {
        switch (props.type) {
            case 'checkbox':
                return (
                    <>
                        <ShopsysCheckbox id="group1item1" name="checkboxWithLink" label="this is a link" />
                        <ShopsysCheckbox id="group1item2" name="checkboxWithLink" label="this is a link" />
                        <ShopsysCheckbox id="group1item3" name="checkboxWithLink" label="this is a link" />
                    </>
                );
            case 'color':
                return (
                    <ProductFilterGroupColorStyled>
                        <ShopsysCheckboxColor
                            color="red"
                            id="group1item1"
                            name="checkboxWithLink"
                            label="this is a link"
                        />
                        <ShopsysCheckboxColor
                            color="yellow"
                            id="group1item2"
                            name="checkboxWithLink"
                            label="this is a link"
                        />
                        <ShopsysCheckboxColor
                            color="black"
                            id="group1item3"
                            name="checkboxWithLink"
                            label="this is a link"
                        />
                    </ProductFilterGroupColorStyled>
                );
            case 'price':
                return (
                    <>
                        <ShopsysRangeSlider
                            min={0}
                            max={1000}
                            onChange={({ min, max }: { min: number; max: number }) =>
                                // eslint-disable-next-line no-console
                                console.log(`min = ${min}, max = ${max}`)
                            }
                        />
                    </>
                );
        }

        throw new Error('Wrong type provided for ShopsysHeading.');
    };

    return (
        <ProductFilterGroupStyled>
            <ProductFilterGroupTitleStyled>
                {props.title}
                <ShopsysIcon icon="arrow-black" iconHeight={12} onClick={handleGroupClick} />
            </ProductFilterGroupTitleStyled>
            <ProductFilterGroupContentStyled isOpen={isGroupOpen}>
                <RenderItems type={props.type} />
            </ProductFilterGroupContentStyled>
        </ProductFilterGroupStyled>
    );
};

/* @component */
export default ProductFilterGroup;
