import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FormLineStyled } from './FormLine.style';
import { FormLineType } from './types';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & FormLineType;
/**
 * A form line element used for wrapping inputs
 */
const FormLine: FC<FormLineProps> = (props) => {
    return (
        <FormLineStyled style={props.style} {...props}>
            {props.children}
        </FormLineStyled>
    );
};

/* @component */
export default FormLine;
