import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { useRef } from 'react';
import { StyleguidePreview, StyleguideSpecimen } from './StyleguideElements';

export const StyleguideSpinboxes: FC = () => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);

    return (
        <StyleguidePreview>
            <StyleguideSpecimen hint="min 1, max 5" label='size="small"'>
                <Spinbox defaultValue={1} id="1" max={5} min={1} ref={spinboxRef} size="small" step={1} />
            </StyleguideSpecimen>
        </StyleguidePreview>
    );
};
