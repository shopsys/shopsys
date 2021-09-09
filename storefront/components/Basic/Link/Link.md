```jsx
import Icon from '../Icon';

<div>
    <Link href="/404">Internal link (does not work because of a breaking router error)</Link>
    <br />
    <Link href="https://www.google.com" linkType="external">
        External link to google
    </Link>
    <br />
    <Link href="/404">
        <Icon icon="NotImplementedYet" iconHeight={16} />
        Internal link with icon (does not work because of a breaking router error)
    </Link>
    <br />
    <Link href="https://www.google.com" linkType="external">
        <Icon icon="NotImplementedYet" iconHeight={16} />
        External link with icon to google
    </Link>
</div>;
```
