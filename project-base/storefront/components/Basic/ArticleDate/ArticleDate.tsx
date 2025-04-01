type ArticleDate = {
    date: string;
    tid?: string;
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid }) => {
    return (
        <time className="font-secondary text-text-subtle text-sm font-semibold" tid={tid}>
            {date}
        </time>
    );
};
