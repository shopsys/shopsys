import { BlogSignpostItemIconStyled, BlogSignpostItemStyled } from 'components/Blocks/BlogSignpost/BlogSignpost.style';
import { BlogCategoryItem } from 'connectors/blogCategories/BlogCategories';
import { FC } from 'react';

type ChildrenProps = {
    blogCategory: BlogCategoryItem;
    activeItem: string;
    itemLevel: number;
};

const Children: FC<ChildrenProps> = (props) => {
    return (
        <>
            {props.blogCategory.children.map((blogCategoryChild) => (
                <>
                    <BlogSignpostItemStyled
                        key={blogCategoryChild.uuid}
                        href={blogCategoryChild.link}
                        isActive={props.activeItem === blogCategoryChild.uuid}
                        itemLevel={props.itemLevel}
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
