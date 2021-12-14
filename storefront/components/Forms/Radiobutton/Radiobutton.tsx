import { FC, InputHTMLAttributes, MouseEventHandler, ReactNode } from 'react';
import { LabelImageWrapper, RadiobuttonStyled } from './Radiobutton.style';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import Image from 'components/Basic/Image';
import { ImageType } from 'types/image';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'value' | 'checked',
    'disabled' | 'id'
>;

type RadiobuttonProps = NativeProps & {
    /**
     * Display Label of the HTML radiobutton element
     */
    label: string | ReactNode | ReactNode[];
    /**
     * A prop which, if present, provides a ImageType object
     * with render values for a label image/icon
     */
    image?: ImageType | null;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
    /**
     * Callback which can be used to uncheck the radiobutton after second click
     */
    uncheckCallback?: () => void;
};

/**
 * An HTML Radiobutton element of type radiobutton
 */
const Radiobutton: FC<RadiobuttonProps> = (props) => {
    const uncheckCallback: MouseEventHandler<HTMLInputElement> = () => {
        if (props.checked && props.uncheckCallback !== undefined) {
            props.uncheckCallback();
        }
    };

    return (
        <LabelWrapper
            htmlFor={props.id === undefined ? props.name + 'radiobutton-id' : props.id}
            label={
                <div>
                    {props.image !== undefined && (
                        <LabelImageWrapper>
                            <Image alt="" image={props.image} />
                        </LabelImageWrapper>
                    )}
                    {props.label}
                </div>
            }
            inputType="radio"
        >
            <RadiobuttonStyled
                {...props.fieldRef}
                {...props}
                id={props.id === undefined ? props.name + 'radiobutton-id' : props.id}
                type="radio"
                onClick={uncheckCallback}
            />
        </LabelWrapper>
    );
};

/* @component */
export default Radiobutton;
