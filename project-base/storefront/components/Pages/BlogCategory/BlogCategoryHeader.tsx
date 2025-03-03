import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

type BlogCategoryHeaderProps = {
    title: string | null | undefined;
    description: string | null;
    image: TypeImageFragment | null;
};

export const BlogCategoryHeader: FC<BlogCategoryHeaderProps> = ({ title, description, image }) => {
    return (
        <Webline className="xxl:max-w-[1432px] mb-6 md:mb-10">
            <div
                className="bg-textAccent rounded-xl"
                style={
                    image?.url
                        ? {
                              background: `linear-gradient(rgba(37, 40, 61, 0.8), rgba(37, 40, 61, 0.8)), url("${image.url}") center/cover no-repeat`,
                          }
                        : undefined
                }
            >
                <div className="xxl:mx-auto xxl:max-w-7xl xxl:px-4 px-5 py-[60px]">
                    <h1 className="text-textInverted mb-3">{title}</h1>
                    {description && (
                        <p
                            className="text-textInverted [&_*]:text-textInverted [&_*]:hover:text-textInverted"
                            dangerouslySetInnerHTML={{ __html: description }}
                        />
                    )}
                </div>
            </div>
        </Webline>
    );
};
