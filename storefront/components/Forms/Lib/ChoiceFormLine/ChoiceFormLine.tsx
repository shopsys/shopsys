import { FC, HTMLAttributes } from 'react';
import { ChoiceFormLineStyled } from './ChoiceFormLine.style';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

/**
 * A form line element used for wrapping inputs
 */
const ChoiceFormLine: FC<NativeProps> = (props) => {
    return <ChoiceFormLineStyled style={props.style}>{props.children}</ChoiceFormLineStyled>;
};

/* @component */
export default ChoiceFormLine;
