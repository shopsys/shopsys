import { RadiobuttonProps } from 'components/Forms/Radiobutton/Radiobutton';

export type RadiobuttonOptionType<T = string> = Pick<
    RadiobuttonProps<T>,
    'disabled' | 'label' | 'value' | 'labelWrapperClassName'
> &
    Partial<Pick<RadiobuttonProps<T>, 'id'>>;
