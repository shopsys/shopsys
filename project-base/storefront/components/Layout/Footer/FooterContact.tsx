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
            className="group flex cursor-pointer gap-4 text-nowrap rounded-lg bg-footer-surface p-4 font-secondary font-semibold text-footer-link no-underline"
            href={href}
            tabIndex={0}
            title={title}
        >
            {icon}

            <div className="flex flex-col">
                <span className="text-footer-link text-lg group-hover:underline">{title}</span>
                <span className="text-footer-foreground text-xs uppercase tracking-wider">{subtitle}</span>
            </div>
        </a>
    );
};
