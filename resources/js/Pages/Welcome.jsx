import React, { useState } from "react";

export default function Welcome() {
    // States here
    const [steamID, setSteamID] = useState("");
    const [userData, setUserData] = useState(null);

    async function getBasicData(event) {
        event.preventDefault();
        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;
        await submitSteamID(steamID, csrfToken);
    }

    async function submitSteamID(steamID, csrfToken) {
        try {
            // Send POST request to the API route
            const response = await fetch("/get-basic-info", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ steamID }),
            });

            const api = await response.json();

            if (api.error) {
                throw new Error(api.error);
            }

            // Extract the first player object from the Steam API response
            const player = api.response.players[0];

            if (!player) {
                throw new Error(
                    "No Steam user found with that ID or URL."
                );
            }

            // Put user data into new object and set state
            setUserData({ ...player });
        } catch (error) {
            console.error("Error fetching Steam data:", error);
        }
    }

    // Styling for the black background and centering
    const containerStyle = {
        minHeight: "100vh",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        background: "#000",
    };

    return (
        <div style={containerStyle}>
            {/* Show either form or username + profile picture depending on whether userData is available */}
            {userData ? (
                <div style={{ color: "#fff" }}>
                    Username: {userData.personaname} <br />
                    <img
                        src={userData.avatarfull}
                    />
                </div>
            ) : (
                <div>
                    <form onSubmit={getBasicData}>
                        <input
                            value={steamID}
                            required
                            placeholder="Enter Steam profile URL, Steam ID, or custom ID"
                            onChange={(event) => setSteamID(event.target.value)}
                        />
                        <button type="submit" style={{color: "#fff"}}>
                            Submit
                        </button>
                    </form>
                </div>
            )}
        </div>
    );
}
