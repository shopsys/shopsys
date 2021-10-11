import {
    BlogSignpostHeadingStyled,
    BlogSignpostItemIconStyled,
    BlogSignpostItemStyled,
    BlogSignpostStyled,
} from './BlogSignpost.style';
import { FC, Fragment } from 'react';
import { BlogCategoriesType } from 'connectors/blogCategories/BlogCategories';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type BlogSingpostProps = {
    blogCategoriesItems: BlogCategoriesType[];
    activeItem: string;
};

const BlogSignpost: FC<BlogSingpostProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <BlogSignpostStyled>
            <BlogSignpostHeadingStyled type="h2">{t('Article categories')}</BlogSignpostHeadingStyled>
            {props.blogCategoriesItems.map((blogCategorie) => (
                <Fragment key={blogCategorie.uuid}>
                    <BlogSignpostItemStyled
                        href={blogCategorie.link}
                        isActive={props.activeItem === blogCategorie.uuid}
                    >
                        <BlogSignpostItemIconStyled icon="Arrow" isActive={props.activeItem === blogCategorie.uuid} />
                        {blogCategorie.name}
                    </BlogSignpostItemStyled>
                    {blogCategorie.children.map((blogCategorieChild) => (
                        <Fragment key={blogCategorieChild.uuid}>
                            <BlogSignpostItemStyled
                                href={blogCategorieChild.link}
                                isActive={props.activeItem === blogCategorieChild.uuid}
                                isChild={true}
                            >
                                <BlogSignpostItemIconStyled
                                    icon="Arrow"
                                    isActive={props.activeItem === blogCategorieChild.uuid}
                                />
                                {blogCategorieChild.name}
                            </BlogSignpostItemStyled>
                        </Fragment>
                    ))}
                </Fragment>
            ))}
        </BlogSignpostStyled>
    );
};

export default BlogSignpost;
