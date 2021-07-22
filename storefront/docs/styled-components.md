We are using styled-components for styling: https://styled-components.com/
### Best practices for styled-component

**modification:**<br/>
We have a special nametag for every modification of component which correlates to the styled components.<br>
We are using extending of parent component: https://styled-components.com/docs/basics#extending-styles

**function:**<br/>
We are using a custom function in style.js for some special modifications, for example with prop size in our button. We can decide which css attributes will be returned.

**css function:**<br/>
https://styled-components.com/docs/api#css<br/>
If you want to use the function in your styled-component you have to use **css** before your code. 
If you don't use **css** then your function doesn't return css attributes

**Destructuring props**<br/>
https://domhabersack.com/styled-components-props-destructuring<br/>
We are using destructuring props in our style.js files.<br/><br/>
**Use this:**
```plain
const Post = styled.article`
  background: ${({ isFeatured, theme }) =>
    isFeatured ? theme.yellow : theme.white
  };
`;
```
**Instead this:**
```plain
const Post = styled.article`
  background: ${props =>
    props.isFeatured ? props.theme.yellow : props.theme.white
  };
`;
```
