import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange', never>;

type SearchInputProps = NativeProps & {
    value: string;
    label: string;
    isLoading: boolean;
    onClear: () => void;
    onSearch?: () => void;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    value,
    isLoading,
    className,
    onChange,
    onClear,
    onSearch,
}) => {
    const { t } = useTranslation();

    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onSearch) {
            onSearch();
        }
    };

    return (
        <div className="relative w-full">
            <input
                autoComplete="off"
                placeholder={label}
                tid={TIDs.layout_header_search_autocomplete_input}
                type="search"
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer mb-0 h-12 w-full rounded border-2 border-white bg-white pr-20 pl-4 text-dark placeholder:text-grey placeholder:opacity-100 focus:outline-none [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    value ? 'pr-20' : 'pr-12',
                    className,
                )}
                onChange={onChange}
                onKeyUp={enterKeyPressHandler}
            />

            {isLoading ? (
                <Loader className="absolute right-4 top-1/2 w-5 -translate-y-1/2 text-dark" />
            ) : (
                <button
                    className="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer border-none"
                    title={t('Search')}
                    type="submit"
                    onClick={onSearch}
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
