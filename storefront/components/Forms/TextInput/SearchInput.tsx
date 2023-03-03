import { SearchTextInputStyled } from './TextInput.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

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
    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onEnterPressCallback !== undefined) {
            onEnterPressCallback();
        }
    };

    return (
        <LabelWrapper label={label} placeholderType="static" htmlFor={dataTestId} inputType="text-input">
            <SearchTextInputStyled
                id={dataTestId}
                onChange={onChange}
                value={value}
                placeholder={label}
                type="search"
                onKeyUp={enterKeyPressHandler}
                data-testid={dataTestId}
                className={className}
            />
            <button
                className="absolute right-4 top-3 cursor-pointer border-none"
                type="submit"
                disabled={isSearchButtonDisabled}
            >
                <Icon iconType="icon" icon="Search" width={20} height={20} />
            </button>
            {isLoading && (
                <div className="absolute top-[calc(50%-16px)] right-4 flex h-8 w-8 items-center justify-center">
                    <Loader iconSize={30} className="text-white" />
                </div>
            )}
        </LabelWrapper>
    );
};
