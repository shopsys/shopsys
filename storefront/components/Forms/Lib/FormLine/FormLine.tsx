import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FormLineProps } from './types';
import { FormLineStyled } from './FormLine.style';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

/**
 * A form line element used for wrapping inputs
 */
const FormLine: FC<FormLineProps & NativeProps> = (props) => {
    return (
        <FormLineStyled style={props.style} {...props}>
            {props.children}
        </FormLineStyled>
    );
};

/* @component */
export default FormLine;
