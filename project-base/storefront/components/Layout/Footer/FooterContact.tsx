type FooterContactProps = {
    icon: React.ReactNode;
    title: string;
    subtitle: string;
    href: string;
    ariaLabel?: string;
};

export const FooterContact: FC<FooterContactProps> = ({ icon, title, subtitle, href, ariaLabel }) => {
    return (
        <a
            aria-label={ariaLabel}
            className="group flex cursor-pointer gap-4 text-nowrap rounded-lg bg-background-default p-4 font-secondary font-semibold text-text-accent no-underline"
            href={href}
            tabIndex={0}
            title={title}
        >
            {icon}

            <div className="flex flex-col">
                <span className="text-lg text-text-accent group-hover:underline">{title}</span>
                <span className="text-text-default text-xs uppercase tracking-wider">{subtitle}</span>
            </div>
        </a>
    );
};
