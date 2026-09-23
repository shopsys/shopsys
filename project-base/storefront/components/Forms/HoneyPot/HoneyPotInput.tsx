import { useFormContext } from 'react-hook-form';

type HoneyPotInputProps = {
    fieldName: string;
};

export const HoneyPotInput: FC<HoneyPotInputProps> = ({ fieldName }) => {
    const formProviderMethods = useFormContext();

    if (!formProviderMethods) {
        throw new Error('HoneyPotInput has to be rendered inside a FormProvider.');
    }

    return (
        <input
            {...formProviderMethods.register(fieldName, { value: '' })}
            aria-hidden="true"
            autoComplete="off"
            className="sr-only"
            tabIndex={-1}
            type="text"
        />
    );
};
