import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { FormLineStyled } from './FormLine.style';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

/**
 * A form line element used for wrapping inputs
 */
const FormLine: FC<NativeProps> = (props) => {
    return <FormLineStyled style={props.style}>{props.children}</FormLineStyled>;
};

/* @component */
export default FormLine;
