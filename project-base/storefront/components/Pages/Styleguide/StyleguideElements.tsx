type StyleguideSectionProps = {
    title: string;
};

export const StyleguideSection: FC<StyleguideSectionProps> = ({ children, className, title }) => (
    <div className="flex flex-col gap-5">
        <h2 className={className}>{title}</h2>

        <div className={className}>{children}</div>
    </div>
);

export const StyleguideSubSection: FC<StyleguideSectionProps> = ({ children, className, title }) => (
    <div className="flex flex-col gap-3">
        <h3 className={className}>{title}</h3>

        <div className={className}>{children}</div>
    </div>
);
