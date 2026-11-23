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
        'flex cursor-pointer select-none items-center justify-center gap-1 rounded-tag px-3.5 py-1.5 font-secondary font-semibold text-sm no-underline transition-all',
        'focus-visible:bg-focus-background focus-visible:text-text-default focus-visible:outline-hidden',
        isActive
            ? 'bg-tag-bg-active text-tag-text-active'
            : 'bg-tag-bg-default text-tag-text-default hover:bg-tag-bg-hovered hover:text-tag-text-hovered hover:no-underline',
        isDisabled && 'pointer-events-none bg-tag-bg-disabled text-tag-text-disabled',
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
