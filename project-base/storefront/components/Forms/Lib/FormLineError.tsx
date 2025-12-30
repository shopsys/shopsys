import { CrossIcon } from 'components/Basic/Icon/CrossIcon';
import { TIDs } from 'cypress/tids';
import { FieldError } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select' | 'dropzone';
    textInputSize?: 'small' | 'default';
    error?: FieldError;
};

export const FormLineError: FC<FormLineErrorProps> = ({ inputType, error, textInputSize }) => {
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
            <CrossIcon
                aria-hidden="true"
                className={twJoin(
                    'text-text-error absolute flex w-4',
                    isInputOrTextArea && `right-3.5 ${isTextInputSmall ? '-top-8' : '-top-9'}`,
                    isInputPassword && `right-12 ${isTextInputSmall ? '-top-8' : '-top-9'}`,
                    isCheckbox && 'top-1/2 right-1 -translate-y-1/2',
                    isSelect && '-top-9 right-11 z-[2]',
                    isDropzone && 'top-1 right-0',
                )}
            />
            {error.message !== undefined && (
                <span
                    className={twJoin('font-secondary text-text-error text-sm', isCheckbox && 'block pr-6')}
                    data-tid={TIDs.form_line_error}
                >
                    {error.message}
                </span>
            )}
        </div>
    );
};
