import { WarningIcon } from 'components/Basic/Icon/WarningIcon';
import { FieldSize } from 'components/Forms/Lib/fieldUtils';
import { TIDs } from 'cypress/tids';
import { FieldError } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select' | 'dropzone';
    textInputSize?: FieldSize;
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

    // a checkbox has no field to hold the icon, so the icon leads the message, which lines up with the label text
    // (20px indicator + 8px gap) and keeps a small gap from the checkbox
    if (isCheckbox) {
        return (
            <div className="mt-1 flex items-start gap-1.5 pl-7">
                <WarningIcon aria-hidden="true" className="mt-0.5 size-4 shrink-0 text-text-error" />
                {error.message !== undefined && (
                    <span id={id} role="alert" className="text-sm text-text-error" data-tid={TIDs.form_line_error}>
                        {error.message}
                    </span>
                )}
            </div>
        );
    }

    return (
        <div className="relative">
            <WarningIcon
                aria-hidden="true"
                className={twJoin(
                    'absolute flex size-5 text-text-error',
                    // Centred in the field above the error: 40px small, 56px default.
                    isInputOrTextArea && ['right-3.5', isTextInputSmall ? '-top-7.5' : '-top-9.5'],
                    isInputPassword && ['right-12', isTextInputSmall ? '-top-7' : '-top-9'],
                    isSelect && '-top-9 right-11 z-2',
                    isDropzone && 'top-1 right-0',
                )}
            />
            {error.message !== undefined && (
                <span id={id} role="alert" className="text-sm text-text-error" data-tid={TIDs.form_line_error}>
                    {error.message}
                </span>
            )}
        </div>
    );
};
