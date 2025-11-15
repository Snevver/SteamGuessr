# ✏️ Frontend Rules

This document outlines the rules to be followed during the development of the frontend. Please adhere to these guidelines to ensure consistency and maintainability.

## 1. HTML

When writing HTML, separate any elements with the same parent with a new line.

For example:

```html
<div>
    <h1>Hello</h1>

    <p>World</p>

    <button>Click me</button>
</div>
```

## 2. React

When you create more than two HTML elements that are similar to eachother, create a React component for them.

## 3. Tailwind CSS

Use Tailwind CSS classes instead of inline styles as much as possible. Also use Tailwind variables like `bg-gray-200` instead of hardcoding colors or other values whenever possible.

When writing Tailwind classes, follow a conventional order: `layout → responsiveness → typography → colors → animations → other → custom classes`

## 4. WCAG

To make sure the website is accessible to everyone, follow the [WCAG guidelines](https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1) on the AA level.