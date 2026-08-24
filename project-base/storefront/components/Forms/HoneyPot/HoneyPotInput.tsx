import { useFormContext } from 'react-hook-form';
import { HONEY_POT_FIELD_NAME } from 'utils/forms/honeyPot';

export const HoneyPotInput: FC = () => {
    const formProviderMethods = useFormContext();

    if (!formProviderMethods) {
        throw new Error('HoneyPotInput has to be rendered inside a FormProvider.');
    }

    return (
        <input
            {...formProviderMethods.register(HONEY_POT_FIELD_NAME, { value: '' })}
            aria-hidden="true"
            autoComplete="off"
            className="sr-only"
            tabIndex={-1}
            type="text"
        />
    );
};
