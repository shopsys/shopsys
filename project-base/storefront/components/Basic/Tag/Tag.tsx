import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ReactElement } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twMergeCustom } from 'utils/twMerge';

type TagProps = {
    isDisabled?: boolean;
    isActive?: boolean;
    ariaLabel?: string;
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
}) => {
    const TagTwClassName = twMergeCustom(
        'flex cursor-pointer items-center justify-center rounded-tag px-4 py-1 font-secondary font-semibold no-underline transition-all',
        'border-tag-border-default bg-tag-bg-default text-sm text-tag-text-default',
        'hover:cursor-pointer hover:border-tag-border-hovered hover:bg-tag-bg-hovered hover:text-tag-text-hovered hover:no-underline',
        isDisabled && 'border-tag-border-disabled bg-tag-bg-disabled text-tag-text-disabled',
        isActive && 'border-tag-border-active bg-tag-bg-active text-tag-text-active',
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
        <button aria-label={ariaLabel} className={TagTwClassName} tabIndex={0} type="button" onClick={onClick}>
            {children}
        </button>
    );

    if (render) {
        return render(content);
    }

    return content;
};
