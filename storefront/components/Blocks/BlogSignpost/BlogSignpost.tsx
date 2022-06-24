import {
    BlogSignpostHeadingStyled,
    BlogSignpostItemIconStyled,
    BlogSignpostItemStyled,
    BlogSignpostStyled,
} from './BlogSignpost.style';
import Children from './Children';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { ListedBlogCategoryType } from 'types/blogCategory';

type BlogSingpostProps = {
    blogCategoryItems?: ListedBlogCategoryType[];
    activeItem: string;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-';

const BlogSignpost: FC<BlogSingpostProps> = (props) => {
    const t = useTypedTranslationFunction();

    return (
        <BlogSignpostStyled>
            <BlogSignpostHeadingStyled type="h2">{t('Article categories')}</BlogSignpostHeadingStyled>
            {props.blogCategoryItems !== undefined &&
                props.blogCategoryItems.map((blogCategory, index) => (
                    <Fragment key={blogCategory.uuid}>
                        <NextLink href={blogCategory.link} passHref>
                            <BlogSignpostItemStyled
                                isActive={props.activeItem === blogCategory.uuid}
                                data-testid={TEST_IDENTIFIER + index}
                            >
                                <BlogSignpostItemIconStyled
                                    iconType="icon"
                                    icon="Arrow"
                                    isActive={props.activeItem === blogCategory.uuid}
                                />
                                {blogCategory.name}
                            </BlogSignpostItemStyled>
                        </NextLink>
                        {blogCategory.children.length > 0 && (
                            <Children blogCategory={blogCategory} activeItem={props.activeItem} itemLevel={1} />
                        )}
                    </Fragment>
                ))}
        </BlogSignpostStyled>
    );
};

export default BlogSignpost;
