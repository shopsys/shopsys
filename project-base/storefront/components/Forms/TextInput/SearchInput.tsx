import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';
import useTranslation from 'next-translate/useTranslation';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange', never>;

type SearchInputProps = NativeProps & {
    value: string;
    label: string;
    shouldShowSpinnerInInput: boolean;
    onClear: () => void;
    onSearch?: () => void;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    value,
    shouldShowSpinnerInInput,
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
                    'peer mb-0 h-12 w-full rounded-md border-2 border-white bg-white pr-20 pl-11 text-dark placeholder:text-skyBlue placeholder:opacity-100 focus:outline-none [&:-internal-autofill-selected]:!bg-white [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-white [&:-webkit-autofill]:!shadow-inner [&:-webkit-autofill]:hover:!bg-white [&:-webkit-autofill]:hover:!shadow-inner [&:-webkit-autofill]:focus:!bg-white [&:-webkit-autofill]:focus:!shadow-inner [&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    value ? 'pr-7' : 'pr-4',
                    className,
                )}
                onChange={onChange}
                onKeyUp={enterKeyPressHandler}
            />

            <button
                className="absolute flex items-center left-3 top-1/2 -translate-y-1/2"
                title={t('Search')}
                type="submit"
                onClick={onSearch}
            >
                <SearchIcon className="w-[18px]" />
            </button>

            {!!value && !shouldShowSpinnerInInput && (
                <div
                    className="absolute right-2 top-1/2 flex -translate-y-1/2 cursor-pointer items-center justify-center text-whiteSnow p-1.5"
                    onClick={onClear}
                >
                    <CloseIcon className="w-3 text-skyBlue" />
                </div>
            )}
            {shouldShowSpinnerInInput && (
                <SpinnerIcon className="absolute right-4 top-1/2 w-5 -translate-y-1/2 text-dark" />
            )}
        </div>
    );
};
