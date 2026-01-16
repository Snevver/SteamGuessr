# 📝 Router Documentation

This document outlines the routes of the application. It can be used to easily find the purpose of a route and the request and response data.

## Web Routes (`routes/web.php`)

 -   **GET /**
 	- **Purpose:** Render the landing page where users can submit their Steam profile (uses Inertia).
 	- **Parameters:** None (standard GET)
 	- **Response:** HTML page rendered via Inertia. The frontend component is `resources/js/Pages/Landing.jsx` and handles the Steam ID submission flow.
 	- **Notes:** This is an entry point for unauthenticated users; it does not return JSON.

---
- **GET /dashboard**
	- **Purpose:** Render the dashboard for users who have been validated via Steam. This route requires a server-side session key `userSteamID` and is protected by the `steam.auth` middleware.
	- **Parameters:** None (standard GET)
	- **Response:** HTML page rendered via Inertia. The frontend component is `resources/js/Pages/Dashboard.jsx` (or `Dashboard.jsx`) and expects the server session to contain `userSteamID`.

---

- **POST /api/classic**
	- **Purpose:** Get a random game from the user's library with hints for the Classic gamemode. This route is protected by the `steam.auth` middleware.
	- **Parameters:** None (standard POST)
		- **Response (JSON body):** Returns a payload with two top-level keys:
			- `hints_data` — the fetched data for all hints keyed by hint name. Each entry contains `hint_name` and `data` (map of key => value).
			- `game` — the normalized game object that the hints correspond to: `{ id, name, cover_url, playtime }`.
		- **Example Response:**
		```json
		{
			"hints_data": {
				"first_letter": {
					"hint_name": "first_letter",
					"data": { "name_first_letter": "G" }
				},
				"release_date": {
					"hint_name": "release_date",
					"data": { "release_date": "2019-03-15" }
				},
				"player_counts": {
					"hint_name": "player_counts",
					"data": { "total_owners": 150000, "current_players": 3421 }
				}
			},
			"game": {
				"id": 12345,
				"name": "Great Game",
				"cover_url": "https://steamcdn-a.akamaihd.net/steam/apps/12345/capsule_616x353.jpg",
			}
		}
		```
		- **Error Response:** If no valid games are found in the session:
		```json
		{
			"error": "No valid games found in session"
		}
		```
		- **Notes:**
			- All hints are returned with their data for the selected game.
			- The game being guessed is selected randomly from `session('allGames')` via the `ValidGameService`.
			- Hint data may be fetched from the Steam Store API, SteamSpy API, or other services as needed.

---

- **POST /initiate-user**
	- **Purpose:** Validate whether a Steam user exists for a provided Steam profile input and store minimal user/session data.
	- **Request (JSON body):**
		- `userSteamID` (required, string) — Steam profile URL, numeric SteamID, or vanity name
		- `isCustomID` (required, boolean) — whether the provided `userSteamID` is a custom vanity name that requires resolution
	- **Validation:** Controller validates `userSteamID` (`required|string`) and `isCustomID` (`required|boolean`).
	- **Behavior:** Controller delegates to the backend services which resolve vanity names when needed and look up the Steam player summary. The controller returns a numeric validation code (see below) as the JSON response body. On success (public profile) it stores a trimmed set of user/stats keys in the session which are exposed to the frontend via the shared Inertia `steam` prop.
	- **Response (JSON body):**
		- `1` — invalid user (not found or vanity resolution failed)
		- `2` — valid Steam user but private profile
		- `3` — valid Steam user with public profile

	Notes:
	- The controller returns a numeric JSON body (e.g. `3`) representing the validation outcome. This differs from an earlier shape that returned an object — treat the response as an integer code.
	- `userSteamID` values are stored and communicated as strings (not raw numbers) to avoid integer precision loss in JavaScript.
	- When a public profile is found the server stores session keys and exposes them via Inertia as `page.props.steam`. The keys available in `steam` are:
		- `steamID` (string|null) — the numeric SteamID as a string
		- `personaState` (string) — a human readable persona state. Keys + values:
    		- 0 => Offline
    		- 1 => Online
    		- 2 => Busy
    		- 3 => Away
    		- 4 => Snooze
    		- 5 => Looking to trade
    		- 6 => Looking to play
		- `publicProfile` (bool)
		- `steamProfileURL` (string|null) — the full Steam profile URL
		- `profilePictureURL` (string|null) — URL to the Steam profile avatar
		- `username` (string|null)
	- `timeCreated` (string|null) — formatted creation date (e.g., "January 1, 2001")
	- `accountAge` (object|null) — breakdown of years/months/days (if available)
	- `totalGamesOwned` (int)
	- `allGames` (array of objects)
		- Each object has the following keys:
			- `id` (int) — the Steam App ID of the game
			- `name` (string) — the name of the game
			- `cover_url` (string|null) — URL to the game's cover image (may be null if unavailable)
			- `playtime` (int) — total playtime in minutes
	- `totalPlaytimeMinutes` (int)
	- `averagePlaytimeMinutes` (int)
	- `topGames` (array)
	- `playedPercentage` (float 0..100)
	- Frontend components can access these values through `usePage().props.steam` (Inertia).

- **GET /admin**
  - **Purpose:** This endpoint is an easteregg. The following endpoints also redirect to /admin:
    - /login
    - /profile
    - /settings
    - /account
