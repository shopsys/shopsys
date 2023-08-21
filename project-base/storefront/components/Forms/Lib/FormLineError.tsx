import { Icon } from 'components/Basic/Icon/Icon';
import { Cross } from 'components/Basic/Icon/IconsSvg';
import { FieldError } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select';
    textInputSize?: 'small' | 'default';
    error?: FieldError;
    dataTestId?: string;
};

const getDataTestId = (dataTestId?: string) => dataTestId ?? 'forms-error';

export const FormLineError: FC<FormLineErrorProps> = ({ inputType, error, dataTestId, textInputSize }) => {
    if (error === undefined) {
        return null;
    }

    const isInputOrTextArea = inputType === 'text-input' || inputType === 'textarea';
    const isInputPassword = inputType === 'text-input-password';
    const isCheckbox = inputType === 'checkbox';
    const isSelect = inputType === 'select';

    const isTextInputSmall = textInputSize === 'small';

    return (
        <div className="relative mt-2" data-testid={getDataTestId(dataTestId)}>
            <Icon
                icon={<Cross />}
                className={twJoin(
                    'absolute flex w-4 text-red',
                    isInputOrTextArea && `right-5 -translate-y-1/2 ${isTextInputSmall ? '-top-8' : '-top-9'}`,
                    isInputPassword && `right-11 -translate-y-1/2 ${isTextInputSmall ? '-top-8' : '-top-9'}`,
                    isCheckbox && '-right-5',
                    isSelect && '-top-10 right-11 z-[2]',
                )}
            />
            {error.message !== undefined && <span className="text-sm text-red">{error.message}</span>}
        </div>
    );
};
