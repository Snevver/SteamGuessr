# Registry system

## Overview

The registry folder is a place where stuff is defined that both the front- and the backend might need. This way we don't need to define things twice.

## Directory structure

```
registry/
└── hints.json          # Central configuration for all game hints and the data needed per hint
```

## Purpose

The registry system serves as a **centralized configuration layer** that:
- Defines shared data structures and configurations used by both frontend and backend
- Maintains consistency across the application by providing a single source of truth
- Enables configuration changes without requiring code modifications
- Reduces duplication by centralizing definitions that multiple parts of the application need

## hints.json

### Structure

The `hints.json` file organizes hints and defines the required data fields for each hint type.


### Hint definition format

Each hint follows this structure:

```json
"hint_name": {
  "neededData": ["field1", "field2", "..."]
}
```

- **hint_name**: A unique identifier for the hint (snake_case)
- **neededData**: An array of data field names required from the Steam API

#### Hints

- `first_letter`: First letter of the game title
  - Required data: `first_letter`
  
- `developer_publisher`: Game creators' information
  - Required data: `developer`, `publisher`
  
- `tags`: Genre and category tags
  - Required data: `tags`
  
- `scrambled_name`: Anagram of the game title
  - Required data: `scrambled_name`

- `total_playtime`: Player's total hours played
  - Required data: `playtime`
  
- `release_date`: When the game was released
  - Required data: `release_date`
  
- `blurred_banner`: Obscured game banner image
  - Required data: `banner_url`

- `reviews`: Review statistics
  - Required data: `review_ratio`, `total_reviews`
  
- `required_space`: Disk space requirements
  - Required data: `required_disk_space`
  
- `player_counts`: Player statistics
  - Required data: `total_owners`, `current_players`

### Benefits

- **Maintainability**: Change hints without modifying code
- **Performance**: Fetch only required data, avoiding unnecessary API calls
- **Consistency**: Frontend and backend always in sync
- **Scalability**: Easy to add new hints or difficulty levels
- **Type Safety**: Clear contract between data requirements and API responses

## Adding new hints

To add a new hint:

1. Add the hint definition to `hints.json` under the appropriate difficulty level
2. Specify all required data fields in `neededData`
3. Ensure the backend service can fetch the specified fields from Steam API
4. Update frontend components to display the new hint type
5. Test the complete flow

### Example: adding a new hint

```json
{
  "hints": {
    "achievements_count": {
      "neededData": ["total_achievements", "unlocked_achievements"]
    }
  }
}
```