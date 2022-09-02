import { LabelImageWrapper, RadiobuttonStyled } from './Radiobutton.style';
import Image from 'components/Basic/Image';
import LabelWrapper from 'components/Forms/Lib/LabelWrapper';
import { FC, InputHTMLAttributes, MouseEventHandler, ReactNode } from 'react';
import { ControllerRenderProps } from 'react-hook-form';
import { ExtractNativePropsFromDefault } from 'typeHelpers/ExtractNativePropsFromDefault';
import { ImageType } from 'types/image';

type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'value' | 'checked',
    'disabled' | 'id'
>;

type RadiobuttonProps = NativeProps & {
    label: string | ReactNode | ReactNode[];
    image?: ImageType | null;
    fieldRef?: ControllerRenderProps<any, any>;
    uncheckCallback?: () => void;
};

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
                            <Image alt="" type="default" image={props.image} />
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

export default Radiobutton;
