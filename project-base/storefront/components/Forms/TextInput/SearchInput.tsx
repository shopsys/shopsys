import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'value', never>;

type SearchInputProps = NativeProps & {
    label: string;
    isSearchButtonDisabled?: boolean;
    onEnterPressCallback?: () => void;
    isLoading: boolean;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    isSearchButtonDisabled,
    onChange,
    value,
    onEnterPressCallback,
    dataTestId,
    isLoading,
    className,
}) => {
    const t = useTypedTranslationFunction();

    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onEnterPressCallback !== undefined) {
            onEnterPressCallback();
        }
    };

    return (
        <LabelWrapper label={label} isWithoutLabel htmlFor={dataTestId} inputType="text-input">
            <input
                id={dataTestId}
                onChange={onChange}
                value={value}
                placeholder={label}
                type="search"
                onKeyUp={enterKeyPressHandler}
                data-testid={dataTestId}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer mb-0 h-12 w-full rounded-xl border-2 border-white bg-white pr-11 pl-4 text-dark placeholder:text-grey placeholder:opacity-100 focus:outline-none [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none [&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none',
                    className,
                )}
            />
            <button
                className="absolute right-4 top-3 cursor-pointer border-none"
                type="submit"
                disabled={isSearchButtonDisabled}
                title={t('Search')}
            >
                <Icon iconType="icon" icon="Search" className="w-5" />
            </button>
            {isLoading && (
                <div className="absolute top-[calc(50%-16px)] right-4 flex h-8 w-8 items-center justify-center">
                    <Loader className="w-8 text-white" />
                </div>
            )}
        </LabelWrapper>
    );
};
