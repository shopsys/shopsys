import { ChoiceFormLineStyled } from './ChoiceFormLine.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

/**
 * A form line element used for wrapping inputs
 */
const ChoiceFormLine: FC<ChoiceFormLineProps> = (props) => {
    return <ChoiceFormLineStyled style={props.style}>{props.children}</ChoiceFormLineStyled>;
};

/* @component */
export default ChoiceFormLine;
