import { StyleguideSection } from './StyleguideElements';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useState } from 'react';

export const StyleguideCheckboxes: FC = () => {
    const [isChecked, setIsChecked] = useState(false);
    const [isChecked2, setIsChecked2] = useState(false);

    return (
        <StyleguideSection className="flex flex-col gap-3" title="Checkboxes">
            <Checkbox
                id="checkboxRegular"
                label="Regular checkbox"
                name="Regular checkbox"
                value={isChecked}
                onChange={() => setIsChecked((currentChecked) => !currentChecked)}
            />
            <Checkbox
                count={2}
                id="checkboxWithCount"
                label="Checkbox with count"
                name="Checkbox with count"
                value={isChecked2}
                onChange={() => setIsChecked2((currentChecked) => !currentChecked)}
            />
            <Checkbox
                disabled
                id="checkboxDisabled"
                label="Disabled checkbox"
                name="Disabled checkbox"
                value={false}
                onChange={() => null}
            />
        </StyleguideSection>
    );
};
