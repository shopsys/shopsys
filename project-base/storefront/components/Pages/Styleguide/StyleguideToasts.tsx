import { Button } from 'components/Forms/Button/Button';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';
import { StyleguidePreview } from './StyleguideElements';

export const StyleguideToasts: FC = () => (
    <StyleguidePreview className="flex flex-wrap gap-3">
        <Button variant="outline" onClick={() => showSuccessMessage('Success message')}>
            Success
        </Button>
        <Button variant="outline" onClick={() => showInfoMessage('Info message')}>
            Info
        </Button>
        <Button variant="outline" onClick={() => showErrorMessage('Error message')}>
            Error
        </Button>
    </StyleguidePreview>
);
