import { Icon } from 'components/Basic/Icon/Icon';
import { FieldError } from 'react-hook-form';
import { twJoin } from 'tailwind-merge';

type FormLineErrorProps = {
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'text-input-password' | 'select';
    textInputSize?: 'small' | 'default';
    error?: FieldError;
    testIdentifier?: string;
};

const getTestIdentifier = (testIdentifier?: string) => testIdentifier ?? 'forms-error';

export const FormLineError: FC<FormLineErrorProps> = ({ inputType, error, testIdentifier, textInputSize }) => {
    if (error === undefined) {
        return null;
    }

    const isInputOrTextArea = inputType === 'text-input' || inputType === 'textarea';
    const isInputPassword = inputType === 'text-input-password';
    const isCheckbox = inputType === 'checkbox';
    const isSelect = inputType === 'select';

    const isTextInputSmall = textInputSize === 'small';

    return (
        <div className="relative mt-2" data-testid={getTestIdentifier(testIdentifier)}>
            <Icon
                iconType="icon"
                icon="Cross"
                width={16}
                height={16}
                className={twJoin(
                    'absolute flex text-red',
                    isInputOrTextArea && `right-5 -translate-y-1/2 ${isTextInputSmall ? '-top-7' : '-top-8'}`,
                    isInputPassword && `right-11 -translate-y-1/2 ${isTextInputSmall ? '-top-7' : '-top-8'}`,
                    isCheckbox && '-right-5',
                    isSelect && '-top-10 right-11 z-[2]',
                )}
            />
            {error.message !== undefined && <span className="text-sm text-red">{error.message}</span>}
        </div>
    );
};
