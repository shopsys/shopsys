import { StyleguideSection } from './StyleguideElements';
import { Button } from 'components/Forms/Button/Button';
import React from 'react';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

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
