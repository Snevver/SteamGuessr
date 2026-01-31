import React, { useState, useEffect } from "react";
import { router } from "@inertiajs/react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";
import Button from "../Components/Button";
import Modal from "../Components/Modal";

export default function Landing() {
    const [userSteamID, setUserSteamID] = useState("");
    const [isCustomID, setIsCustomID] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState(null);
    const [isValidInput, setIsValidInput] = useState(null);
    const [isHelpModalOpen, setIsHelpModalOpen] = useState(false);
    const [swipeOut, setSwipeOut] = useState(false);
    const [isReferred, setIsReferred] = useState(false);

    /**
     * Validates if the input is a valid Steam ID or URL and if so, returns a big amount of user data.
     * Accepts:
     * - Full Steam profile URL: https://steamcommunity.com/profiles/76561198000000000
     * - Full custom Steam URL: https://steamcommunity.com/id/customname
     * - Steam ID only: 76561198000000000 (17 digits, typically starts with 7656119)
     * - Custom Steam name only: customname (alphanumeric, hyphens, underscores)
     * @param {string} value - The value to validate.
     * @returns {boolean} - True if valid, false otherwise.
     */
    function isValidUserSteamID(value) {
        const trimmedValue = value.trim();

        if (!value || trimmedValue.length === 0) {
            return null;
        }

        if (trimmedValue.length > 3) {
            if (
                /^7656119\d{10}$/.test(trimmedValue) ||
                /^https?:\/\/(www\.)?steamcommunity\.com\/profiles\/(7656119\d{10})\/?$/.test(
                    trimmedValue
                )
            ) {
                // Numeric ID
                setIsCustomID(false);
                return true;
            } else if (
                /^[a-zA-Z0-9_-]+$/.test(trimmedValue) ||
                /^https?:\/\/(www\.)?steamcommunity\.com\/id\/([a-zA-Z0-9_-]+)\/?$/.test(
                    trimmedValue
                )
            ) {
                // Custom ID
                setIsCustomID(true);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Submits the User Steam ID to the API route to check if the User Steam ID is valid.
     * @param {Event} event - The event object, used to prevent the default behavior.
     */
    async function submitUserSteamID(event) {
        event.preventDefault();
        setIsLoading(true);
        setError(null);

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        try {
            // Send POST request to the API route to check if the User Steam ID is valid
            const rawResponse = await fetch("/initiate-user", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    userSteamID,
                    isCustomID: isCustomID,
                }),
            });

            const responseCode = await rawResponse.json();

            // 1 = invalid user id (not found)
            // 2 = valid user, but private profile
            // 3 = valid user, public profile
            if (responseCode === 3) {
                // Redirect to dashboard
                setSwipeOut(true);

                setTimeout(() => {
                    router.visit("/dashboard");
                }, 300);
            } else if (responseCode === 2) {
                // Tell user to set profile to public
                setError(
                    "The profile you entered is not public. Please set your profile visibility to public in your privacy settings."
                );
            } else if (responseCode === 1) {
                // User not found
                setError("User not found");
            } else {
                setError("Unexpected response from server");
            }
        } catch (error) {
            console.error("Error submitting User Steam ID:", error);
            setError(error.message);
        } finally {
            setIsLoading(false);
        }
    }

    // Check if the page was referred
    useEffect(() => {
        if (window.location.hash === "#referred") {
            setIsReferred(true);
        }
    }, []);

    // Updates the validation state when user steamID changes.
    useEffect(() => {
        const validation = isValidUserSteamID(userSteamID);
        setIsValidInput(validation);
        // Clear error when input becomes valid
        if (validation === true && error) {
            setError(null);
        }
    }, [userSteamID]);

    return (
        <Layout isLandingPage={true} swipeOut={swipeOut}>
            {/* User Steam ID Input */}
            <Card
                className={`space-y-6 w-full max-w-2xl ${
                    isReferred ? "animate-swipe-in" : "animate-fade-in"
                }`}
            >
                <div className="text-center space-y-2">
                    <h3 className="text-2xl font-semibold text-white">
                        Enter Your Steam Profile
                    </h3>

                    <p className="text-gray-400 text-sm">
                        Paste your Steam profile URL, Steam ID, or custom ID to
                        get started.{" "}
                        <button
                            onClick={() => setIsHelpModalOpen(true)}
                            className="text-blue-500 hover:underline"
                        >
                            Need help?
                        </button>
                    </p>
                </div>

                <form className="space-y-4" onSubmit={submitUserSteamID}>
                    <div className="relative">
                        <input
                            className={`w-full px-4 py-3 bg-gray-800/50 border rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed ${
                                isValidInput === false
                                    ? "border-red-500 focus:ring-red-500 focus:border-red-500"
                                    : isValidInput === true
                                    ? "border-green-500 focus:ring-green-500 focus:border-green-500"
                                    : "border-gray-700 focus:ring-blue-500 focus:border-transparent"
                            }`}
                            id="steam-id-input"
                            type="text"
                            value={userSteamID}
                            required
                            placeholder="https://steamcommunity.com/id/yourprofile"
                            onChange={(event) =>
                                setUserSteamID(event.target.value)
                            }
                            disabled={isLoading}
                        />
                    </div>

                    {error && (
                        <div className="p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                            {error}
                        </div>
                    )}

                    <Button
                        type="submit"
                        disabled={isLoading}
                        ariaLabel="Submit User Steam ID"
                    >
                        {isLoading ? (
                            <span className="flex items-center justify-center">
                                <svg
                                    className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        className="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        strokeWidth="4"
                                    ></circle>
                                    <path
                                        className="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                Hang tight...
                            </span>
                        ) : (
                            "Get Started"
                        )}
                    </Button>
                </form>
            </Card>

            {/* Help Modal */}
            <Modal
                isOpen={isHelpModalOpen}
                onClose={() => setIsHelpModalOpen(false)}
                title="Need help finding your Steam ID?"
            >
                <div className="space-y-1">
                    <p className="text-xl font-semibold text-white">
                        Where to find your Steam ID:
                    </p>

                    <ul className="space-y-1 text-white">
                        <li>1. Open Steam and navigate to your profile page</li>

                        <li>
                            2. Click on the URL in the top left to copy it, or
                            right click on your profile and select "Copy Page
                            URL"
                        </li>

                        <li>
                            3. Paste the URL into the input field and click "Get
                            Started"
                        </li>
                    </ul>
                </div>

                <div className="space-y-1">
                    <p className="text-xl font-semibold text-white">
                        You can use any of these formats:
                    </p>

                    <ul className="space-y-1 text-white">
                        <li>
                            • Your full Steam profile URL:{" "}
                            <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                https://steamcommunity.com/profiles/76561198000000000
                            </span>
                        </li>

                        <li>
                            • Your full custom Steam URL:{" "}
                            <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                https://steamcommunity.com/id/customname
                            </span>
                        </li>

                        <li>
                            • Your Steam ID only:{" "}
                            <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                76561198000000000
                            </span>
                        </li>

                        <li>
                            • Your custom Steam name only:{" "}
                            <span className="bg-gray-900/60 px-2 py-1 rounded-md font-mono break-all inline-block">
                                customname
                            </span>
                        </li>
                    </ul>
                </div>
            </Modal>
        </Layout>
    );
}
