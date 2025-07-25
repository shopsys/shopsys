type FooterContactProps = {
    icon: React.ReactNode;
    title: string;
    subtitle: string;
    href: string;
    ariaLabel: string;
};

export const FooterContact: FC<FooterContactProps> = ({ icon, title, subtitle, href, ariaLabel }) => {
    return (
        <a
            aria-label={ariaLabel}
            className="bg-background-default group text-text-accent font-secondary flex cursor-pointer gap-4 rounded-lg p-4 font-semibold text-nowrap no-underline"
            href={href}
            tabIndex={0}
            title={title}
        >
            {icon}

            <div className="flex flex-col">
                <span className="text-text-accent text-lg group-hover:underline">{title}</span>
                <span className="text-text-default text-xs tracking-wider uppercase">{subtitle}</span>
            </div>
        </a>
    );
};
