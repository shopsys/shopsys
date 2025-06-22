# JavaScript Utilities

The Shopsys Administration panel provides several JavaScript utility classes that enhance user experience with interactive features.
These utilities are automatically initialized and provide consistent behavior across the administration interface.

[TOC]

## Modal Window System

The modal window system consists of two main parts that work together to provide modal dialogs and confirmation windows.

### ModalWindow Class

The `ModalWindow` class provides a flexible foundation for creating modal dialogs with customizable content, styling, and button configurations.

#### Basic Usage

```javascript
import ModalWindow from '@shopsys/administration/src/js/utils/modalWindow';

// Simple notification modal
new ModalWindow({
    content: 'Operation completed successfully!',
});

// Modal with title and custom style
new ModalWindow({
    title: 'Warning',
    content: 'This action cannot be undone.',
    style: 'warning',
});
```

#### Configuration Options

| Option    | Type   | Default | Description                                                                    |
| --------- | ------ | ------- | ------------------------------------------------------------------------------ |
| `content` | string | `''`    | HTML content for modal body                                                    |
| `title`   | string | `null`  | Optional modal title                                                           |
| `size`    | string | `'sm'`  | Modal size: `sm`, `md`, `lg`, or `xl`                                          |
| `style`   | string | `null`  | Modal style: `primary`, `secondary`, `success`, `danger`, `warning`, or `info` |
| `buttons` | Array  | `[]`    | Array of button configurations                                                 |

#### Button Configuration

Each button can be configured with the following properties:

```javascript
{
    text: 'Button Text',        // Required: button label
    style: 'primary',           // Optional: button style (default: 'secondary')
    href: '#',                  // Optional: URL to navigate to (default: '#')
    event: () => { /* ... */ }  // Optional: function to execute on click
}
```

#### Status Icons

When a `style` is specified, the modal automatically displays an appropriate status icon:

- `danger` → Exclamation circle (red)
- `warning` → Alert triangle (yellow)
- `success` → Check mark (green)
- `info` → Info circle (blue)
- `primary` → Info circle (blue)

#### Complete Example

```javascript
new ModalWindow({
    title: 'Delete Item',
    content: 'Are you sure you want to delete this item?',
    style: 'danger',
    size: 'md',
    buttons: [
        { text: 'Cancel' },
        {
            text: 'Delete',
            style: 'danger',
            event: () => {
                // Perform deletion logic
                console.log('Item deleted');
            },
        },
    ],
});
```

### ConfirmWindow Class

The `ConfirmWindow` class provides a specialized modal for confirmation dialogs with both programmatic and declarative usage patterns.

#### Programmatic Usage

```javascript
import ConfirmWindow from '@shopsys/administration/src/js/utils/confirmWindow';

ConfirmWindow.show({
    content: 'Do you really want to delete this item?',
    continueEvent: () => {
        // Handle confirmation
        deleteItem();
    },
});
```

#### Configuration Options

| Option          | Type     | Default           | Description                        |
| --------------- | -------- | ----------------- | ---------------------------------- |
| `title`         | string   | `'Are you sure?'` | Modal title                        |
| `content`       | string   | `''`              | Confirmation message               |
| `style`         | string   | `'danger'`        | Modal style                        |
| `continueUrl`   | string   | `'#'`             | URL to navigate to when confirmed  |
| `continueEvent` | function | `null`            | Function to execute when confirmed |
| `textContinue`  | string   | `'Yes'`           | Continue button text               |
| `textCancel`    | string   | `'No'`            | Cancel button text                 |

#### Declarative Usage (Data Attributes)

Add data attributes to any clickable element to automatically show confirmation dialogs:

```html
<a
    href="/delete/123"
    data-confirm-window
    data-confirm-message="Do you really want to delete this item?"
    data-confirm-style="danger"
>
    Delete Item
</a>
```

#### Data Attributes

| Attribute                   | Description                               | Default          |
| --------------------------- | ----------------------------------------- | ---------------- |
| `data-confirm-window`       | Required: enables confirmation behavior   | -                |
| `data-confirm-message`      | Confirmation message content              | `''`             |
| `data-confirm-style`        | Modal style (danger, warning, info, etc.) | `'danger'`       |
| `data-confirm-continue-url` | URL to navigate to when confirmed         | Element's `href` |

## Copy to Clipboard

The `CopyToClipboard` class provides one-click copying functionality with visual feedback through tooltips.

### Usage

Add the data attribute to any element to enable copy-to-clipboard functionality:

```html
<button data-js-clipboard-copy="Text to copy">Copy this text</button>

<!-- With custom tooltip title -->
<button data-js-clipboard-copy="API_KEY_12345" data-js-clipboard-copy-title="Copy API key">Copy API Key</button>
```

### Data Attributes

| Attribute                      | Description                       | Required |
| ------------------------------ | --------------------------------- | -------- |
| `data-js-clipboard-copy`       | Text content to copy to clipboard | Yes      |
| `data-js-clipboard-copy-title` | Custom tooltip text               | No       |

### Behavior

1. **Initial state**: Shows tooltip with copy instruction
2. **After click**: Shows "Copied!" confirmation tooltip
3. **Mouse leave**: Restores original tooltip text
4. **Error handling**: Logs errors to console if clipboard access fails

## Recommended Length Counter

The `RecommendedLength` class provides character count feedback for input fields and textareas with recommended length limits.

### Usage

Add the data attribute to input fields or textareas:

```html
<input type="text" data-js-recommended-length="160" placeholder="Enter meta description" />

<textarea data-js-recommended-length="300" placeholder="Enter product description"></textarea>
```

### Features

- **Real-time counting**: Updates character count as user types
- **Placeholder support**: Counts placeholder text length
- **Debounced updates**: Optimized performance with debouncing
- **Responsive positioning**: Adapts to input-group layouts

### Display Format

The counter displays in the format:

```
Used: 45 characters. Recommended max. 160
```
