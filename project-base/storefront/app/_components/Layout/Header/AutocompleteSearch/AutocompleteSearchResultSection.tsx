type AutocompleteSearchResultSectionProps = {
    title: string;
    isSlider?: boolean;
};

export const AutocompleteSearchResultSection: FC<AutocompleteSearchResultSectionProps> = ({
    title,
    children,
    tid,
    isSlider = false,
}) => {
    return (
        <section className="flex flex-col gap-2.5" tid={tid}>
            <h5>{title}</h5>

            {isSlider ? children : <ul className="flex flex-wrap gap-2">{children}</ul>}
        </section>
    );
};
