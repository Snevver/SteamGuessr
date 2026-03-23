# ✏️ Best Practices and Naming Conventions

This document outlines the best practices and naming conventions to be followed during the development of this project. Please adhere to these guidelines to ensure consistency and maintainability.

## 1. Code and file naming conventions

-   **Files and folders:** Use snake-case for file and folder names (e.g., `my-file-name.js`).
-   **JavaScript:** In JavaScript or TypeScript, things like variables and functions should be named using camelCase (e.g., `myVariableName`).
-   **Classes and components:** Use PascalCase for class names and React components (e.g., `MyComponent`).

#### Variables

When naming variables, please avoid using acronyms or abbreviations unless they are widely recognized (e.g., `id` for identifier). Long and descriptive names may seem cumbersome, but they enhance code readability and maintainability.

Also make sure variable names are never too simple, like `data` or `info`. Instead, use names that clearly indicate the purpose of the variable, such as `userData` or `productInfo`. This also guarantees that variable names won't conflict with eachother in larger files.

#### Functions

Functions should use verbs and be clear about what action it performs. Below is a short list of prefixes you can use to describe the intention of the function:

-   Retrieve data: `get`
-   Insert data: `add`
-   Update data: `update`
-   Retrieve from external source: `fetch`

For example: `function addUserData() { ... }`

Make sure the parameters of the function have a specified type and a default value, and if the function returns something, make sure to specify the return type as well.

## 2. Proper coding

### Coding principles

To write clean and maintainable code, please follow these principles:

-   **DRY (Don't Repeat Yourself):** Avoid code duplication by creating reusable functions or components.
-   **KISS (Keep It Simple, Stupid):** Write simple and straightforward code. Avoid unnecessary complexity.
-   **YAGNI (You Aren't Gonna Need It):** Don't add functionality until it is necessary.

### Commenting

Make sure to comment your code properly. It should be easy for someone else to understand any snippet of code as soon as they read it. This is why it's important to write comments if the code is not self-explanatory or if it requires additional context.

You can use the following structure for comments:

```
//======================================================================
// CATEGORY LARGE FONT
//======================================================================

//-----------------------------------------------------
// Sub-Category Smaller Font
//-----------------------------------------------------

/* TITLE HERE */

/* Sub-title Here, Notice the First Letters are Capitalized */

# Option 1
# Option 2
# Option 3

/*
 * This is a detailed explanation
 * of something that should require
 * several paragraphs of information
 */

// This is a single line quote
```

Don't use the category large and sub-category smaller font comments too often, since they can easily clutter the code.

Make sure to always start your comment with a space and a capital letter. You do not need to finish every comment with a period.

## 3. Temporary files

Avoid committing temporary files that don't need to be in the repository by adding them to the `.gitignore` file. If you do need to add a temporary file to the repository, please put it in the `temp` folder.

## 4. Markdown files and other documentation

When writing markdown files or other documentation, always put them in the `docs` folder. If needed, refer to them in the `README.md` file.

## 5. Tasks

Don't start working on a new feature without first creating a task for it on the [designated Jira board](https://snevver.atlassian.net/jira/software/projects/SCRUM/summary). Always write down how long you expect this task to take, the priority of it, and a short description of what needs to be done.

## 6. Branches

There are three main branches in this repository:

-   `development`: This is the development branch. All new features and bug fixes should be merged into this branch first.
-   `testing`: This is the testing branch. Once features and fixes in the `development` branch work and are verified, they should be merged into this branch for further testing.
-   `beta`: This is the beta branch. Once features and fixes in the `testing` branch are verified, they should be merged into this branch.
-   `production`: This is the production branch. It should always contain stable and tested code.

When developing new features, pull the latest version of the `development` branch and create a new branch from it. Once your feature is complete and tested, create a pull request to merge your changes back into the `development` branch. The best practices for creating pull requests are outlined below.

### Branch naming conventions

When creating branches, use the following format:

`<author-name>/<type>/<short-description>`

Where `<type>` can be one of the following:

-   `feat` for new features
-   `fix` for bug fixes
-   `docs` for documentation changes
-   `style` for code style changes (formatting, missing semi-colons, etc.)
-   `ref` for code refactoring
-   `test` for adding or updating tests
-   `other` for other changes

Make sure to put dashes in the short description instead of spaces.

For example: `john/feat/add-login`

## 7. Commit messages

When writing commit messages, use the same format as branch names:

`<type>/<short description>`

Make sure to write a clear and concise description that summarizes all changes made in the commit.

For example: `Feat/Correct login bug` - `Fixed the bug where users could not log in with valid credentials.`

## 8. Pull requests

When creating a pull request, use this template:

Title: `<type>/<short description>`

```markdown
## Description

<!-- A summary of the changes, and anything that the reviewer should know about the changes. -->

## List of changes

<!-- List of changes, each on a new line. -->

## Type of change

<!-- Remove all change types that are not applicable to the changes. -->

-   Bug fix(es) (non-breaking change which fixes an issue)
-   New feature(s) (non-breaking change which adds functionality)
-   Breaking change(s) (fix or feature that would cause existing functionality to not work as expected)
-   Code refactoring (This is a change that improves the code without adding any new functionality)
-   This change requires a documentation update
```

Always make sure to assign at least one team member to review your pull request. Apply any requested changes, and wait for the reviewer to resolve all comments before merging.

## 9. Specific best practices

Both the backend and the frontend have their own specific best practices. These are outlined in the [backend](backend.md) and [frontend](frontend.md) documents respectively.
