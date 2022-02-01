import { BlogSignpostItemIconStyled, BlogSignpostItemStyled } from 'components/Blocks/BlogSignpost/BlogSignpost.style';
import { FC } from 'react';
import { ListedBlogCategoryType } from 'types/blogCategory';

type ChildrenProps = {
    blogCategory: ListedBlogCategoryType;
    activeItem: string;
    itemLevel: number;
};

const Children: FC<ChildrenProps> = (props) => {
    const testIdentifier = 'blocks-blogsignpost-children-';

    return (
        <>
            {props.blogCategory.children.map((blogCategoryChild, index) => (
                <>
                    <BlogSignpostItemStyled
                        key={blogCategoryChild.uuid}
                        href={blogCategoryChild.link}
                        isActive={props.activeItem === blogCategoryChild.uuid}
                        itemLevel={props.itemLevel}
                        data-testid={testIdentifier + index}
                    >
                        <BlogSignpostItemIconStyled
                            iconType="icon"
                            icon="Arrow"
                            isActive={props.activeItem === blogCategoryChild.uuid}
                        />
                        {blogCategoryChild.name}
                    </BlogSignpostItemStyled>
                    {blogCategoryChild.children.length > 0 && (
                        <Children
                            blogCategory={blogCategoryChild}
                            activeItem={props.activeItem}
                            itemLevel={props.itemLevel + 1}
                        />
                    )}
                </>
            ))}
        </>
    );
};

export default Children;
