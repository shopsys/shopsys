import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

type BlogCategoryHeaderProps = {
    title: string | null | undefined;
    description: string | null;
    image: TypeImageFragment | null;
};

export const BlogCategoryHeader: FC<BlogCategoryHeaderProps> = ({ title, description, image }) => {
    return (
        <Webline width="xxl">
            <div
                className="bg-text-accent rounded-xl"
                style={
                    image?.url
                        ? {
                              background: `linear-gradient(rgba(37, 40, 61, 0.8), rgba(37, 40, 61, 0.8)), url("${image.url}") center/cover no-repeat`,
                          }
                        : undefined
                }
            >
                <Webline className="py-14">
                    <h1 className="text-text-inverted mb-3">{title}</h1>
                    {description && (
                        <p
                            className="text-text-inverted [&_*]:text-text-inverted [&_*]:hover:text-text-inverted"
                            dangerouslySetInnerHTML={{ __html: description }}
                        />
                    )}
                </Webline>
            </div>
        </Webline>
    );
};
