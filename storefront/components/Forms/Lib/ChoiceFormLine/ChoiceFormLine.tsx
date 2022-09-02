import { ChoiceFormLineStyled } from './ChoiceFormLine.style';
import { FC, HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

const ChoiceFormLine: FC<ChoiceFormLineProps> = (props) => {
    return <ChoiceFormLineStyled style={props.style}>{props.children}</ChoiceFormLineStyled>;
};

export default ChoiceFormLine;
