import { CloseIcon, SearchIcon } from 'components/Basic/Icon/IconsSvg';
import { Loader } from 'components/Basic/Loader/Loader';
import useTranslation from 'next-translate/useTranslation';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'helpers/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange', never>;

type SearchInputProps = NativeProps & {
    value: string;
    label: string;
    isSearchButtonDisabled?: boolean;
    isLoading: boolean;
    onClear: () => void;
    onEnterPressCallback?: () => void;
};

const TEST_IDENTIFIER = 'layout-header-search-autocomplete-input';

export const SearchInput: FC<SearchInputProps> = ({
    label,
    isSearchButtonDisabled,
    value,
    isLoading,
    className,
    onChange,
    onClear,
    onEnterPressCallback,
}) => {
    const { t } = useTranslation();

    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onEnterPressCallback !== undefined) {
            onEnterPressCallback();
        }
    };

    return (
        <div className="relative w-full">
            <input
                id={TEST_IDENTIFIER}
                onChange={onChange}
                value={value}
                placeholder={label}
                type="search"
                autoComplete="off"
                onKeyUp={enterKeyPressHandler}
                data-testid={TEST_IDENTIFIER}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer mb-0 h-12 w-full rounded border-2 border-white bg-white pr-20 pl-4 text-dark placeholder:text-grey placeholder:opacity-100 focus:outline-none [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none [&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none',
                    value ? 'pr-20' : 'pr-12',
                    className,
                )}
            />

            {isLoading ? (
                <Loader className="absolute right-4 top-1/2 w-5 -translate-y-1/2 text-dark" />
            ) : (
                <button
                    className="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer border-none"
                    type="submit"
                    disabled={isSearchButtonDisabled}
                    title={t('Search')}
                >
                    <SearchIcon className="w-5" />
                </button>
            )}

            {!!value && (
                <div
                    className="absolute right-11 top-1/2 flex -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-greyLighter p-2"
                    onClick={onClear}
                >
                    <CloseIcon />
                </div>
            )}
        </div>
    );
};
