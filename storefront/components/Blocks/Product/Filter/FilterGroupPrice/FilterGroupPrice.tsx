import RangeSlider from 'components/Basic/RangeSlider';
import {
    FilterGroupArrowStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { FC, useState } from 'react';

type FilterGroupPriceProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * Maximal price of price slider
     */
    maximalPrice: number;
    /**
     * Minimal price of price slider
     */
    minimalPrice: number;
};

const FilterGroupPrice: FC<FilterGroupPriceProps> = (props) => {
    const testIdentifier = 'blocks-product-filter-filtergroup-price';

    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled data-testid={testIdentifier}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <RangeSlider min={props.minimalPrice} max={props.maximalPrice} delay={300} />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupPrice;
