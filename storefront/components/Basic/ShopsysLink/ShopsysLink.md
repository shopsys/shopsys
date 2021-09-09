
```jsx
import Icon from '../Icon';

<div>
    <ShopsysLink href="/404">
        Internal link (does not work because of a breaking router error)
    </ShopsysLink>
    <br />
    <ShopsysLink href="https://www.google.com" linkType="external">
        External link to google
    </ShopsysLink>
    <br />
    <ShopsysLink href="/404">
        <Icon icon="NotImplementedYet" iconHeight={16} />
        Internal link with icon (does not work because of a breaking router error)
    </ShopsysLink>
    <br />
    <ShopsysLink href="https://www.google.com" linkType="external" icon="info" iconHeight={16}>
        <Icon icon="NotImplementedYet" iconHeight={16} />
        External link with icon to google
    </ShopsysLink>
</div>
```
