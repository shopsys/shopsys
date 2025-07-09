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
    ariaLabelForSearchButton: string;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    value,
    shouldShowSpinnerInInput,
    className,
    onChange,
    onClear,
    onSearch,
    ariaLabelForSearchButton,
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
                aria-label={label}
                autoComplete="off"
                data-tid={TIDs.layout_header_search_autocomplete_input}
                id="search-input"
                placeholder={label}
                type="search"
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'border-input-border-default bg-input-bg-default text-input-text-default placeholder:text-input-placeholder-default peer mb-0 h-12 w-full rounded-md border pr-20 pl-11',
                    '[&:-internal-autofill-selected]:!bg-input-bg-default [&:-webkit-autofill]:!bg-input-bg-default [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!shadow-inner',
                    '[&:-webkit-autofill]:hover:!bg-input-bg-hovered [&:-webkit-autofill]:hover:!shadow-inner',
                    '[&:-webkit-autofill]:focus:!bg-input-fill [&:-webkit-autofill]:focus:!shadow-inner',
                    '[&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    'focus:outline-hidden',
                    value ? 'pr-7' : 'pr-4',
                    className,
                )}
                onChange={onChange}
                onKeyUp={enterKeyPressHandler}
            />

            <button
                aria-label={ariaLabelForSearchButton}
                className="gjs-template-header-search-button absolute top-1/2 left-0 flex size-11 -translate-y-1/2 items-center justify-center rounded-sm"
                tabIndex={0}
                title={t('Search')}
                type="submit"
                onClick={onSearch}
            >
                <SearchIcon className="text-icon-less hover:text-icon-accent size-4" />
            </button>

            {!!value && !shouldShowSpinnerInInput && (
                <button
                    aria-label={t('Clear search input')}
                    className="absolute top-1/2 right-2 flex -translate-y-1/2 cursor-pointer items-center justify-center p-1.5"
                    tabIndex={0}
                    title={t('Clear search')}
                    type="button"
                    onClick={onClear}
                >
                    <CloseIcon className="text-icon-less hover:text-icon-accent size-4" />
                </button>
            )}
            {shouldShowSpinnerInInput && (
                <SpinnerIcon
                    aria-label={t('Loading search results')}
                    className="text-icon-less absolute top-1/2 right-3 size-5 -translate-y-1/2"
                />
            )}
        </div>
    );
};
