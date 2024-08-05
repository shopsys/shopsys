import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ReactElement } from 'react';
import { PageType } from 'store/slices/createPageLoadingStateSlice';
import { twJoin } from 'tailwind-merge';

type LabelLinkProps = {
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

export const LabelLink: FC<LabelLinkProps> = ({
    href,
    type,
    children,
    isDisabled,
    isActive,
    className,
    render,
    onClick,
}) => {
    const labelLinkTwClassName = twJoin(
        'px-4 py-1 rounded-full no-underline transition-all flex justify-center items-center',
        'bg-labelLinkBackground text-labelLinkText border-labelLinkBorder',
        'hover:bg-labelLinkBackgroundHovered hover:text-labelLinkTextHovered hover:border-labelLinkBorderHovered hover:no-underline hover:cursor-pointer',
        isDisabled && 'bg-labelLinkBackgroundDisabled text-labelLinkTextDisabled border-labelLinkBorderDisabled',
        isActive && 'bg-labelLinkBackgroundActive text-labelLinkTextActive border-labelLinkBorderActive',
        className,
    );

    if (href) {
        return (
            <ExtendedNextLink className={labelLinkTwClassName} href={href} type={type}>
                {children}
            </ExtendedNextLink>
        );
    }

    const content = (
        <div className={labelLinkTwClassName} onClick={onClick}>
            {children}
        </div>
    );

    if (render) {
        return render(content);
    }

    return content;
};
