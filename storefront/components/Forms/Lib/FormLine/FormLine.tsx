import { FormLineStyled } from './FormLine.style';
import { FormLinePropType } from './propTypes';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type FormLineProps = NativeProps & FormLinePropType;

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
