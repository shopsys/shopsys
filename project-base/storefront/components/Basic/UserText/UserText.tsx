type UserTextProps = {
    htmlContent: string;
};

export const UserText: FC<UserTextProps> = ({ htmlContent }) => (
    <section className="user-text" dangerouslySetInnerHTML={{ __html: htmlContent }} />
);
