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

When you create more than two HTML elements that are similar to each other, create a React component for them.

-   **JavaScript:** In JavaScript or TypeScript, things like variables and functions should be named using camelCase (e.g., `myVariableName`).
-   **Classes and components:** Use PascalCase for class names and React components (e.g., `MyComponent`).

## 3. Tailwind CSS

Use Tailwind CSS classes instead of inline styles as much as possible. Also use Tailwind variables like `bg-gray-200` instead of hardcoding colors or other values whenever possible.

When writing Tailwind classes, follow a conventional order: `layout → responsiveness → typography → colors → animations → other → custom classes`


## 4. JSDoc

When writing JavaScript functions, always use JSDoc comments to describe it. This helps other developers understand the purpose and usage of the function.

Make sure you:

-   Describe the function's purpose
-   List every parameter with its type and a brief description
-   Specify the return type and a brief description of what is returned

For example:

```javascript
/**
 * Calculates the sum of two numbers.
 * @param {number} a - The first number.
 * @param {number} b - The second number.
 * @return {number} The sum of the two numbers.
 */
function calculateSum(a, b) {
    return a + b;
}
```

A cheat sheet for JSDoc can be found [here](https://devhints.io/jsdoc).


## 5. WCAG

To make sure the website is accessible to everyone, follow the [WCAG guidelines](https://www.w3.org/WAI/WCAG22/quickref/?versions=2.1) on the AA level.