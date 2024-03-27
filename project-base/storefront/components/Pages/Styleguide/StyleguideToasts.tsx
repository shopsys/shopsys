import { StyleguideSection } from './StyleguideElements';
import { Button } from 'components/Forms/Button/Button';
import { showErrorMessage, showSuccessMessage, showInfoMessage } from 'helpers/toasts';
import React from 'react';

export const StyleguideToasts: FC = () => {
    return (
        <StyleguideSection title="Toasts">
            <Button
                onClick={() => {
                    showErrorMessage('Error message');
                    showSuccessMessage('Success message');
                    showInfoMessage('Info message');
                }}
            >
                Show Toasts
            </Button>
        </StyleguideSection>
    );
};
