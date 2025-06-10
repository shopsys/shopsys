import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ReactElement } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twMergeCustom } from 'utils/twMerge';

type TagProps = {
    isDisabled?: boolean;
    isActive?: boolean;
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
                render: (input: JSX.Element) => ReactElement<any, any> | null;
            }
          | {
                render?: never;
            }
      ))
);

export const Tag: FC<TagProps> = ({ href, type, children, isDisabled, isActive, className, render, onClick }) => {
    const TagTwClassName = twMergeCustom(
        'px-4 py-1 rounded-tag no-underline transition-all flex justify-center items-center font-semibold font-secondary cursor-pointer',
        'bg-tag-bg-default text-tag-text-default border-tag-border-default text-sm',
        'hover:bg-tag-bg-hovered hover:text-tag-text-hovered hover:border-tag-border-hovered hover:no-underline hover:cursor-pointer',
        'focus-visible:outline-tag-border-hovered focus-visible:outline-2',
        isDisabled && 'bg-tag-bg-disabled text-tag-text-disabled border-tag-border-disabled',
        isActive && 'bg-tag-bg-active text-tag-text-active border-tag-border-active',
        className,
    );

    if (href) {
        return (
            <ExtendedNextLink className={TagTwClassName} href={href} type={type}>
                {children}
            </ExtendedNextLink>
        );
    }

    const content = (
        <div className={TagTwClassName} onClick={onClick}>
            {children}
        </div>
    );

    if (render) {
        return render(content);
    }

    return content;
};
