import { TextareaStyled } from './Textarea.style';
import { getStateAfterValidation } from 'components/Forms/Helpers/getStateAfterValidation';
import { LabelWrapper } from 'components/Forms/Lib/LabelWrapper/LabelWrapper';
import { FC, TextareaHTMLAttributes, useEffect, useState } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<
    TextareaHTMLAttributes<HTMLTextAreaElement>,
    'rows',
    'disabled' | 'required' | 'name' | 'id' | 'style'
>;

type TextareaProps = NativeProps & {
    label: string;
    hasError: boolean;
    isTouched: boolean;
    markSuccessfulWhenValid?: boolean;
    fieldRef?: ControllerRenderProps<any, any>;
};

export const Textarea: FC<TextareaProps> = (props) => {
    const [inputState, setInputState] = useState<'success' | 'error' | undefined>(undefined);

    useEffect(() => {
        setInputState(getStateAfterValidation(props.hasError, props.isTouched, props.markSuccessfulWhenValid));
    }, [props.hasError, props.isTouched, props.markSuccessfulWhenValid]);

    return (
        <LabelWrapper {...props} htmlFor={props.id} inputType="textarea">
            <TextareaStyled {...props.fieldRef} {...props} inputState={inputState} placeholder={props.label} />
        </LabelWrapper>
    );
};
