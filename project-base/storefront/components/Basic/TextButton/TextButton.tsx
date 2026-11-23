import { getLinkClassName, LinkStyleOptions } from 'components/Basic/Link/linkUtils';
import { ButtonHTMLAttributes } from 'react';
import { twMergeCustom } from 'utils/twMerge';

type TextButtonProps = Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'type'> &
    LinkStyleOptions & {
        type?: 'button' | 'submit';
    };

/**
 * An action that looks like a text link (show more, reset filters, back, open a popup). It keeps the button
 * semantics and takes the same `variant` and `tone` as `Link` (`action` by default, as a text action rarely
 * sits inside a sentence); anything that navigates is a `Link`.
 */
export const TextButton: FC<TextButtonProps> = ({
    variant = 'action',
    tone,
    type = 'button',
    className,
    tid,
    children,
    ...props
}) => (
    <button
        className={twMergeCustom(getLinkClassName({ variant, tone }), className)}
        data-tid={tid}
        type={type}
        {...props}
    >
        {children}
    </button>
);
