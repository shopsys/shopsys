import { SearchButtonStyled, SearchTextInputStyled, TextInputLoadingWrapper } from './TextInput.style';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { theme } from 'components/Theme/main';
import { FC, InputHTMLAttributes, KeyboardEventHandler } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<InputHTMLAttributes<HTMLInputElement>, 'onChange' | 'value', never>;

type SearchInputProps = NativeProps & {
    label: string;
    isSearchButtonDisabled?: boolean;
    onEnterPressCallback?: () => void;
    testIdentifier: string;
    isLoading: boolean;
};

export const SearchInput: FC<SearchInputProps> = ({
    label,
    isSearchButtonDisabled,
    onChange,
    value,
    onEnterPressCallback,
    testIdentifier,
    isLoading,
}) => {
    const enterKeyPressHandler: KeyboardEventHandler<HTMLInputElement> = (event) => {
        if (event.key === 'Enter' && onEnterPressCallback !== undefined) {
            onEnterPressCallback();
        }
    };

    return (
        <LabelWrapper label={label} placeholderType="static" htmlFor={testIdentifier} inputType="text-input">
            <SearchTextInputStyled
                id={testIdentifier}
                onChange={onChange}
                value={value}
                placeholder={label}
                type="search"
                onKeyUp={enterKeyPressHandler}
                data-testid={testIdentifier}
            />
            <SearchButtonStyled type="submit" disabled={isSearchButtonDisabled}>
                <Icon iconType="icon" icon="Search" />
            </SearchButtonStyled>
            {isLoading && (
                <TextInputLoadingWrapper>
                    <Loader iconSize={30} color={theme.color.white} />
                </TextInputLoadingWrapper>
            )}
        </LabelWrapper>
    );
};
