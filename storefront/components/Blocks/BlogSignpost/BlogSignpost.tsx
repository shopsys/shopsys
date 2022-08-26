import {
    BlogSignpostHeadingStyled,
    BlogSignpostItemIconStyled,
    BlogSignpostItemStyled,
    BlogSignpostStyled,
} from './BlogSignpost.style';
import { Children } from './Children/Children';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import NextLink from 'next/link';
import { FC, Fragment } from 'react';
import { ListedBlogCategoryType } from 'types/blogCategory';

type BlogSingpostProps = {
    blogCategoryItems?: ListedBlogCategoryType[];
    activeItem: string;
};

const TEST_IDENTIFIER = 'blocks-blogsignpost-';

export const BlogSignpost: FC<BlogSingpostProps> = ({ blogCategoryItems, activeItem }) => {
    const t = useTypedTranslationFunction();

    return (
        <BlogSignpostStyled>
            <BlogSignpostHeadingStyled type="h2">{t('Article categories')}</BlogSignpostHeadingStyled>
            {blogCategoryItems !== undefined &&
                blogCategoryItems.map((blogCategory, index) => (
                    <Fragment key={blogCategory.uuid}>
                        <NextLink href={blogCategory.link} passHref>
                            <BlogSignpostItemStyled
                                isActive={activeItem === blogCategory.uuid}
                                data-testid={TEST_IDENTIFIER + index}
                            >
                                <BlogSignpostItemIconStyled
                                    alt=""
                                    iconType="icon"
                                    icon="Arrow"
                                    isActive={activeItem === blogCategory.uuid}
                                />
                                {blogCategory.name}
                            </BlogSignpostItemStyled>
                        </NextLink>
                        {blogCategory.children.length > 0 && (
                            <Children blogCategory={blogCategory} activeItem={activeItem} itemLevel={1} />
                        )}
                    </Fragment>
                ))}
        </BlogSignpostStyled>
    );
};
