type ArticleDate = {
    date: string;
    tid?: string;
};

export const ArticleDate: FC<ArticleDate> = ({ date, tid }) => {
    return (
        <time className="font-secondary text-text-less text-sm font-semibold" tid={tid}>
            {date}
        </time>
    );
};
