import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';
import { InputHTMLAttributes, KeyboardEventHandler, RefObject } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange', never>;

type SearchInputProps = NativeProps & {
    value: string;
    label: string;
    shouldShowSpinnerInInput: boolean;
    className?: string;
    inputId?: string;
    inputRef?: RefObject<HTMLInputElement | null>;
    onClear: () => void;
    onSearch?: () => void;
    onOpenPopup?: () => void;
    ariaLabelForSearchButton: string;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    value,
    shouldShowSpinnerInInput,
    className,
    inputId = 'search-input',
    inputRef,
    onChange,
    onClear,
    onSearch,
    onOpenPopup,
    ariaLabelForSearchButton,
}) => {
    const { t } = useTranslation();
    const shouldShowClearButton = !!value && !shouldShowSpinnerInInput;

    const handleKeyDown: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onSearch) {
            onSearch();
        } else if (onOpenPopup && event.key.length === 1) {
            onOpenPopup();
        }
    };

    const handleClear = () => {
        onClear();
        inputRef?.current?.focus();
    };

    return (
        <div className="relative w-full">
            <input
                aria-label={label}
                autoComplete="off"
                data-tid={TIDs.layout_header_search_autocomplete_input}
                id={inputId}
                placeholder={label}
                ref={inputRef}
                type="search"
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'peer mb-0 h-12 w-full rounded-input border-2 border-input-border-default bg-input-bg-default pr-20 pl-11 text-input-text-default placeholder:text-input-placeholder-default',
                    '[&:-internal-autofill-selected]:bg-input-bg-default! [&:-internal-autofill-selected]:shadow-inner! [&:-webkit-autofill]:bg-input-bg-default! [&:-webkit-autofill]:shadow-inner!',
                    '[&:-webkit-autofill]:hover:bg-input-bg-hovered! [&:-webkit-autofill]:hover:shadow-inner!',
                    '[&:-webkit-autofill]:focus:bg-input-fill! [&:-webkit-autofill]:focus:shadow-inner!',
                    '[&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    'focus:outline-hidden',
                    shouldShowClearButton ? 'pr-10' : 'pr-4',
                    className,
                )}
                onChange={onChange}
                onClick={onOpenPopup}
                onKeyDown={handleKeyDown}
            />

            <button
                aria-label={ariaLabelForSearchButton}
                className="group gjs-template-header-search-button absolute top-1/2 left-1 flex size-10 -translate-y-1/2 items-center justify-center rounded-md hover:cursor-pointer"
                tabIndex={0}
                title={t('Search')}
                type="submit"
                onClick={onSearch}
            >
                <SearchIcon className="size-6 text-icon-less group-hover:text-icon-accent" />
            </button>

            {shouldShowClearButton && (
                <button
                    aria-label={t('Clear search input', { ns: 'accessibility' })}
                    className="absolute top-1/2 right-2 flex -translate-y-1/2 cursor-pointer items-center justify-center rounded-md p-1.5"
                    tabIndex={0}
                    title={t('Clear search')}
                    type="button"
                    onClick={handleClear}
                >
                    <CloseIcon className="size-4 text-icon-less hover:text-icon-default" />
                </button>
            )}
            {shouldShowSpinnerInInput && (
                <SpinnerIcon
                    aria-label={t('Loading search results', { ns: 'accessibility' })}
                    className="absolute top-1/2 right-3 size-5 -translate-y-1/2 text-icon-less"
                />
            )}
        </div>
    );
};
