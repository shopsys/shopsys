export const ArticleTitle: FC = ({ dataTestId, children }) => (
    <h1 className="text-bi mb-6 block px-5 text-5xl font-bold leading-10 text-primary" data-testid={dataTestId}>
        {children}
    </h1>
);
