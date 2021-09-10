import { FC, InputHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import LabelWrapper from '../Lib/LabelWrapper';
import { RadiobuttonStyled } from './Radiobutton.style';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    never,
    'disabled' | 'id' | 'name' | 'value'
>;

type RadiobuttonProps = NativeProps & {
    /**
     * Display Label of the HTML radiobutton element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * A prop which, if present, provides a URL for an image
     * which then gets rendered next to the label
     */
    image?: string;
};

/**
 * An HTML Radiobutton element of type radiobutton
 */
const Radiobutton: FC<RadiobuttonProps> = (props) => {
    return (
        <LabelWrapper
            htmlFor={props.id}
            label={
                <div>
                    {props.image !== undefined && <img alt="" src={props.image} />}
                    <span>{props.label}</span>
                </div>
            }
            inputType="radio"
        >
            <RadiobuttonStyled {...props} type="radio" />
        </LabelWrapper>
    );
};

/* @component */
export default Radiobutton;
