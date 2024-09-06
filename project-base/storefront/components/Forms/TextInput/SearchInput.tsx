import { SearchIcon } from 'components/Basic/Icon/SearchIcon';
import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import useTranslation from 'next-translate/useTranslation';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange', never>;

type SearchInputProps = NativeProps & {
    value: string;
    label: string;
    shouldShowSpinnerInInput: boolean;
    onSearch?: () => void;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    value,
    shouldShowSpinnerInInput,
    className,
    onChange,
    onSearch,
}) => {
    const { t } = useTranslation();

    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onSearch) {
            onSearch();
        }
    };

    return (
        <div className="relative w-full h-12 flex items-center mb-5 border-2 border-inputBorder rounded-md px-2.5">
            <input
                autoComplete="off"
                placeholder={label}
                type="search"
                value={value}
                className={twMergeCustom(
                    'h-full w-full text-inputText placeholder:text-inputPlaceholder',
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

            {shouldShowSpinnerInInput ? (
                <SpinnerIcon className="w-5 text-inputText"/>
            ) : (
                <button
                    className="flex items-center"
                    title={t('Search')}
                    type="submit"
                    onClick={onSearch}
                >
                    <SearchIcon className="w-5 text-inputBorder"/>
                </button>
            )}
        </div>
    );
};
