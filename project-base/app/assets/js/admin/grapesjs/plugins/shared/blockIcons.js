const svg = path => `<svg style="width:48px;height:48px" viewBox="0 0 24 24">${path}</svg>`;

export const textBlockIcon = svg('<path fill="currentColor" d="M6,5H18V7H13V19H11V7H6V5Z" />');

export const imageBlockIcon = svg(
    '<path fill="currentColor" d="M8.5,13.5L11,16.5L14.5,12L19,18H5M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19Z" />',
);

export const productsBlockIcon = svg(
    '<path fill="currentColor" d="M1.5,7H8V17H1.5V7M2.4,15.4H7.1L5.7,13.1L4.8,14.3L3.9,13.2L2.4,15.4M8.75,7H15.25V17H8.75V7M9.65,15.4H14.35L12.95,13.1L12.05,14.3L11.15,13.2L9.65,15.4M16,7H22.5V17H16V7M16.9,15.4H21.6L20.2,13.1L19.3,14.3L18.4,13.2L16.9,15.4Z" />',
);

export const column1BlockIcon = svg(
    '<rect x="5" y="4" width="14" height="16" fill="none" stroke="currentColor" stroke-width="1.4" />',
);

export const column2BlockIcon = svg(
    '<path fill="none" stroke="currentColor" stroke-width="1.4" d="M4,4H10V20H4V4ZM14,4H20V20H14V4Z" />',
);

export const column3BlockIcon = svg(
    '<path fill="none" stroke="currentColor" stroke-width="1.4" d="M3,4H7.8V20H3V4ZM9.6,4H14.4V20H9.6V4ZM16.2,4H21V20H16.2V4Z" />',
);
