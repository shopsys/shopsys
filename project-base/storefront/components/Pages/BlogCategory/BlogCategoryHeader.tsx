import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

type BlogCategoryHeaderProps = {
    title: string | null | undefined;
    description: string | null;
    image: TypeImageFragment | null;
};

export const BlogCategoryHeader: FC<BlogCategoryHeaderProps> = ({ title, description, image }) => {
    return (
        <Webline className="mb-6 md:mb-10 xxl:max-w-[1432px]">
            <div
                className="rounded-xl bg-textAccent bg-cover bg-center bg-no-repeat"
                style={{ backgroundImage: `url("${image?.url}")` }}
            >
                <div className="px-5 py-[60px] xxl:mx-auto xxl:max-w-7xl xxl:px-4">
                    <h1 className="mb-3 text-textInverted">{title}</h1>
                    <p className="text-textInverted">{description}</p>
                </div>
            </div>
        </Webline>
    );
};
