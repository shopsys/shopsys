import { Button } from 'components/Forms/Button/Button';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { showWarningMessage } from 'utils/toasts/showWarningMessage';
import { StyleguidePreview } from './StyleguideElements';

export const StyleguideToasts: FC = () => (
    <StyleguidePreview className="flex flex-wrap gap-3">
        <Button variant="outline" onClick={() => showSuccessMessage('Success message')}>
            Success
        </Button>
        <Button variant="outline" onClick={() => showWarningMessage('Warning message')}>
            Warning
        </Button>
        <Button variant="outline" onClick={() => showErrorMessage('Error message')}>
            Error
        </Button>
    </StyleguidePreview>
);
