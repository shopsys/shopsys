import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { type AriaRole, type ReactElement, type Ref } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twMergeCustom } from 'utils/twMerge';

type TagProps = {
    isDisabled?: boolean;
    isActive?: boolean;
    ariaLabel?: string;
    ariaPressed?: boolean;
    ariaSelected?: boolean;
    buttonRef?: Ref<HTMLButtonElement>;
    htmlRole?: AriaRole;
    onClick?: () => void;
} & (
    | {
          href: string;
          type: PageType | undefined;
          render?: never;
      }
    | ({
          href?: never;
          type?: never;
      } & (
          | {
                render: (input: ReactElement) => ReactElement<any, any> | null;
            }
          | {
                render?: never;
            }
      ))
);

export const Tag: FC<TagProps> = ({
    href,
    type,
    children,
    isDisabled,
    isActive,
    className,
    render,
    onClick,
    ariaLabel,
    ariaPressed,
    ariaSelected,
    buttonRef,
    htmlRole,
}) => {
    const TagTwClassName = twMergeCustom(
        'flex cursor-pointer select-none items-center justify-center gap-2 rounded-2xl px-3.5 py-1.5 font-secondary font-semibold text-sm no-underline transition-all',
        'focus-visible:bg-orange-500 focus-visible:text-text-default focus-visible:outline-hidden',
        isActive
            ? 'bg-background-brand-less text-text-inverted'
            : 'bg-background-more text-text-default hover:bg-background-most hover:no-underline',
        isDisabled && 'pointer-events-none opacity-50',
        className,
    );

    if (href) {
        return (
            <ExtendedNextLink className={TagTwClassName} href={href} type={type} onClick={onClick}>
                {children}
            </ExtendedNextLink>
        );
    }

    const content = (
        <button
            ref={buttonRef}
            aria-label={ariaLabel}
            aria-pressed={ariaPressed}
            aria-selected={ariaSelected}
            className={TagTwClassName}
            role={htmlRole}
            tabIndex={0}
            type="button"
            onClick={onClick}
        >
            {children}
        </button>
    );

    if (render) {
        return render(content);
    }

    return content;
};
