import { CloseIcon } from 'components/Basic/Icon/CloseIcon';
import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { IconButton } from 'components/Forms/Button/IconButton';
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
    onClearEmpty?: () => void;
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
    onClearEmpty,
    onSearch,
    onOpenPopup,
    ariaLabelForSearchButton,
}) => {
    const { t } = useTranslation();
    const shouldShowClearButton = (!!value || !!onClearEmpty) && !shouldShowSpinnerInInput;

    const handleKeyDown: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onSearch) {
            onSearch();
        } else if (onOpenPopup && event.key.length === 1) {
            onOpenPopup();
        }
    };

    const handleClear = () => {
        if (value) {
            onClear();
            inputRef?.current?.focus();

            return;
        }

        onClearEmpty?.();
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

            <IconButton
                Icon={SearchIcon}
                ariaLabel={ariaLabelForSearchButton}
                className="gjs-template-header-search-button absolute top-1/2 left-1 -translate-y-1/2"
                shape="rounded"
                title={t('Search')}
                type="submit"
                variant="ghost"
                onClick={onSearch}
            />

            {shouldShowClearButton && (
                <IconButton
                    Icon={CloseIcon}
                    aria-label={value ? t('Clear search input', { ns: 'accessibility' }) : t('Close')}
                    className="absolute top-1/2 right-2 -translate-y-1/2"
                    shape="rounded"
                    title={value ? t('Clear search') : t('Close')}
                    variant="ghost"
                    onClick={handleClear}
                />
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
