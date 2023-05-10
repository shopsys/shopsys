import { HTMLAttributes } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';

type NativeProps = ExtractNativePropsFromDefault<HTMLAttributes<HTMLDivElement>, never, 'style'>;

type ChoiceFormLineProps = NativeProps;

export const ChoiceFormLine: FC<ChoiceFormLineProps> = ({ children, style }) => (
    <div className="mb-4 w-fit" style={style}>
        {children}
    </div>
);
