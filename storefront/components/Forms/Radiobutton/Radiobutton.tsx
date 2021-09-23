import { FC, InputHTMLAttributes, MouseEventHandler, ReactNode } from 'react';
import { LabelImageWrapper, RadiobuttonStyled } from './Radiobutton.style';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import Image from 'components/Basic/Image';
import { ImageType } from 'components/Basic/Image/types';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'id' | 'value',
    'disabled' | 'checked'
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
    image?: ImageType | null;
    /**
     * a ref of the controlled field element used for hooking onto the field events/changes
     */
    fieldRef?: ControllerRenderProps;
    /**
     * Callback which can be used to uncheck the radiobutton after second click
     */
    onSecondClickCallback?: () => void;
};

/**
 * An HTML Radiobutton element of type radiobutton
 */
const Radiobutton: FC<RadiobuttonProps> = (props) => {
    const onSecondClickHandler: MouseEventHandler<HTMLInputElement> = (event) => {
        if (event.currentTarget.checked && props.onSecondClickCallback !== undefined) {
            props.onSecondClickCallback();
        }
    };

    return (
        <LabelWrapper
            htmlFor={props.id}
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
                name={props.name}
                id={props.id}
                value={props.value}
                disabled={props.disabled}
                checked={props.checked}
                type="radio"
                onClick={onSecondClickHandler}
            />
        </LabelWrapper>
    );
};

/* @component */
export default Radiobutton;
