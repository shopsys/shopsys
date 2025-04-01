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
        <div className="border-border-default relative w-full rounded-md border">
            <input
                autoComplete="off"
                placeholder={label}
                tid={TIDs.layout_header_search_autocomplete_input}
                type="search"
                value={value}
                className={twMergeCustom(
                    // class "peer" is used for styling in LabelWrapper
                    'border-input-bg-default bg-input-bg-default text-input-text-default placeholder:text-input-placeholder-default peer mb-0 h-12 w-full rounded-md border-2 pr-20 pl-11',
                    '[&:-internal-autofill-selected]:!bg-input-bg-default [&:-webkit-autofill]:!bg-input-bg-default [&:-internal-autofill-selected]:!shadow-inner [&:-webkit-autofill]:!shadow-inner',
                    '[&:-webkit-autofill]:hover:!bg-input-bg-hovered [&:-webkit-autofill]:hover:!shadow-inner',
                    '[&:-webkit-autofill]:focus:!bg-inputFill [&:-webkit-autofill]:focus:!shadow-inner',
                    '[&::-webkit-cancel-button]:appearance-none [&::-webkit-results-button]:appearance-none [&::-webkit-results-decoration]:appearance-none [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-decoration]:appearance-none [&::-webkit-search-results-button]:appearance-none [&::-webkit-search-results-decoration]:appearance-none',
                    'focus:outline-hidden',
                    value ? 'pr-7' : 'pr-4',
                    className,
                )}
                onChange={onChange}
                onKeyUp={enterKeyPressHandler}
            />

            <button
                className="gjp-template-header-search-button absolute top-1/2 left-3 flex -translate-y-1/2 items-center"
                title={t('Search')}
                type="submit"
                onClick={onSearch}
            >
                <SearchIcon className="text-input-placeholder-default w-4" />
            </button>

            {!!value && !shouldShowSpinnerInInput && (
                <div
                    className="absolute top-1/2 right-2 flex -translate-y-1/2 cursor-pointer items-center justify-center p-1.5"
                    onClick={onClear}
                >
                    <CloseIcon className="text-input-text-disabled w-4" />
                </div>
            )}
            {shouldShowSpinnerInInput && (
                <SpinnerIcon className="text-input-text-default absolute top-1/2 right-3 w-5 -translate-y-1/2" />
            )}
        </div>
    );
};
