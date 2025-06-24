import { twMergeCustom } from 'utils/twMerge';

type AccessibleLinkProps = {
    title: string;
    href: string;
};

export const AccessibleLink: FC<AccessibleLinkProps> = ({ title, href, className }) => {
    return (
        <a
            href={href}
            tabIndex={0}
            className={twMergeCustom(
                'absolute left-0 -translate-x-[1500rem] focus-visible:top-0 focus-visible:translate-x-0',
                'bg-bg-orange-500 text-text-default font-secondary z-aboveOverlay w-full p-2 text-center font-semibold no-underline',
                className,
            )}
        >
            {title}
        </a>
    );
};
