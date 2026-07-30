import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { TIDs } from 'cypress/tids';
import { FieldError } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select' | 'dropzone';
    textInputSize?: 'small' | 'default';
    error?: FieldError;
    id?: string;
};

export const FormLineError: FC<FormLineErrorProps> = ({ inputType, error, textInputSize, id }) => {
    if (error === undefined) {
        return null;
    }

    const isInputOrTextArea = inputType === 'text-input' || inputType === 'textarea';
    const isInputPassword = inputType === 'text-input-password';
    const isCheckbox = inputType === 'checkbox';
    const isSelect = inputType === 'select';
    const isDropzone = inputType === 'dropzone';

    const isTextInputSmall = textInputSize === 'small';

    return (
        <div className="relative">
            <WarningIcon
                aria-hidden="true"
                className={twJoin(
                    'absolute flex size-5 text-text-error',
                    isInputOrTextArea && `right-3.5 ${isTextInputSmall ? '-top-8.5' : '-top-9.5'}`,
                    isInputPassword && `right-12 ${isTextInputSmall ? '-top-8' : '-top-9'}`,
                    isCheckbox && 'top-1/2 right-1 -translate-y-1/2',
                    isSelect && '-top-9 right-11 z-2',
                    isDropzone && 'top-1 right-0',
                )}
            />
            {error.message !== undefined && (
                <span
                    id={id}
                    role="alert"
                    className={twJoin('font-secondary text-sm text-text-error', isCheckbox && 'block pr-6')}
                    data-tid={TIDs.form_line_error}
                >
                    {error.message}
                </span>
            )}
        </div>
    );
};
