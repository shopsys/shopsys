import { FC, ReactNode } from 'react';

type ArticleTitleProps = {
    dataTestid: string;
    children: ReactNode;
};

export const ArticleTitle: FC<ArticleTitleProps> = ({ dataTestid, children }) => (
    <h1 className="text-bi mb-6 block px-5 text-5xl font-bold leading-10 text-primary" data-testid={dataTestid}>
        {children}
    </h1>
);
