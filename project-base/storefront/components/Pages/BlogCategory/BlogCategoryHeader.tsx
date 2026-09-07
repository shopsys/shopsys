import { Image } from 'components/Basic/Image/Image';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeImageFragment } from 'graphql/requests/images/fragments/ImageFragment.generated';

type BlogCategoryHeaderProps = {
    title: string | null | undefined;
    description: string | null;
    image: TypeImageFragment | null;
    imageAlt: string;
};

export const BlogCategoryHeader: FC<BlogCategoryHeaderProps> = ({ title, description, image, imageAlt }) => {
    return (
        <Webline>
            <div className="relative overflow-hidden rounded-xl bg-background-brand after:absolute after:inset-0 after:block after:bg-background-brand/80 after:content-['']">
                {image && <Image fill alt={imageAlt} className="object-cover" sizes="100vw" src={image.url} />}
                <Webline className="relative z-above px-10 py-14">
                    <h1 className="mb-3 text-text-inverted">{title}</h1>
                    {description && (
                        <p
                            className="text-text-inverted **:text-text-inverted **:hover:text-text-inverted"
                            dangerouslySetInnerHTML={{ __html: description }}
                        />
                    )}
                </Webline>
            </div>
        </Webline>
    );
};
