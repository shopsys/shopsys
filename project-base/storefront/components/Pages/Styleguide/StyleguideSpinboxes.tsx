import { StyleguideSection } from './StyleguideElements';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import React, { useRef } from 'react';

export const StyleguideSpinboxes: FC = () => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);

    return (
        <StyleguideSection className="flex flex-col gap-3" title="Spinboxes">
            <Spinbox defaultValue={1} id="1" max={5} min={1} ref={spinboxRef} size="small" step={1} />
        </StyleguideSection>
    );
};
