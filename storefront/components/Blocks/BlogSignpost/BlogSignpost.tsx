import {
    BlogSignpostHeadingStyled,
    BlogSignpostItemIconStyled,
    BlogSignpostItemStyled,
    BlogSignpostStyled,
} from './BlogSignpost.style';
import { FC, Fragment } from 'react';
import { BlogCategoryItem } from 'connectors/blogCategories/BlogCategories';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type BlogSingpostProps = {
    blogCategoriesItems?: BlogCategoryItem[];
    activeItem: string;
};

const BlogSignpost: FC<BlogSingpostProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <BlogSignpostStyled>
            <BlogSignpostHeadingStyled type="h2">{t('Article categories')}</BlogSignpostHeadingStyled>
            {props.blogCategoriesItems !== undefined &&
                props.blogCategoriesItems.map((blogCategory) => (
                    <Fragment key={blogCategory.uuid}>
                        <BlogSignpostItemStyled
                            href={blogCategory.link}
                            isActive={props.activeItem === blogCategory.uuid}
                        >
                            <BlogSignpostItemIconStyled
                                iconType="icon"
                                icon="Arrow"
                                isActive={props.activeItem === blogCategory.uuid}
                            />
                            {blogCategory.name}
                        </BlogSignpostItemStyled>
                        {blogCategory.children.map((blogCategoryChild) => (
                            <BlogSignpostItemStyled
                                key={blogCategoryChild.uuid}
                                href={blogCategoryChild.link}
                                isActive={props.activeItem === blogCategoryChild.uuid}
                                isChild={true}
                            >
                                <BlogSignpostItemIconStyled
                                    iconType="icon"
                                    icon="Arrow"
                                    isActive={props.activeItem === blogCategoryChild.uuid}
                                />
                                {blogCategoryChild.name}
                            </BlogSignpostItemStyled>
                        ))}
                    </Fragment>
                ))}
        </BlogSignpostStyled>
    );
};

export default BlogSignpost;
