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
                    'peer mb-0 h-12 w-full rounded-md border-2 border-inputBackground bg-inputBackground pl-11 pr-20 text-inputText placeholder:text-inputPlaceholder',
                    '[&:-internal-autofill-selected]:!bg-inputBackground [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!bg-inputBackground [&:-webkit-autofill]:!shadow-inner',
                    '[&:-webkit-autofill]:hover:!bg-inputBackgroundHovered [&:-webkit-autofill]:hover:!shadow-inner',
                    '[&:-webkit-autofill]:focus:!bg-inputBackgroundActive [&:-webkit-autofill]:focus:!shadow-inner',
                    '[&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    'focus:outline-none',
                    value ? 'pr-7' : 'pr-4',
                    className,
                )}
                onChange={onChange}
                onKeyUp={enterKeyPressHandler}
            />

            <button
                className="absolute left-3 top-1/2 flex -translate-y-1/2 items-center"
                title={t('Search')}
                type="submit"
                onClick={onSearch}
            >
                <SearchIcon className="w-[18px]" />
            </button>

            {!!value && !shouldShowSpinnerInInput && (
                <div
                    className="absolute right-2 top-1/2 flex -translate-y-1/2 cursor-pointer items-center justify-center p-1.5"
                    onClick={onClear}
                >
                    <CloseIcon className="w-4 text-inputTextDisabled" />
                </div>
            )}
            {shouldShowSpinnerInInput && (
                <SpinnerIcon className="absolute right-3 top-1/2 w-5 -translate-y-1/2 text-inputText" />
            )}
        </div>
    );
};
