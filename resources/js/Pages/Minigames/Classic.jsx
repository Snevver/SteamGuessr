import React, { useState, useEffect, useMemo } from "react";
import { usePage } from "@inertiajs/react";
import Layout from "../../Layouts/Layout";
import Modal from "../../Components/Modal";
import Card from "../../Components/Card";
import Button from "../../Components/Button";

const CLASSIC_TUTORIAL_SEEN_KEY = "classicTutorialSeen";

export default function Classic() {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);
    const [gameData, setGameData] = useState(null);
    const [swipeOut, setSwipeOut] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [guess, setGuess] = useState("");
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showSuggestions, setShowSuggestions] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(-1);
    const [isOver, setIsOver] = useState(false);
    const [attemptCount, setAttemptCount] = useState(0);
    const [result, setResult] = useState(null); // "win" | "lose" | null
    const [statusMessage, setStatusMessage] = useState("");
    const [obscuredIndices, setObscuredIndices] = useState([]);
    const { steam } = usePage().props;

    // The code below was made with the help of AI
    // Filter games based on input
    const filteredGames = React.useMemo(() => {
        if (!guess.trim() || !steam?.allGames) {
            return [];
        }

        const searchTerm = guess.toLowerCase().trim();

        return steam.allGames
            .filter((game) => game.name.toLowerCase().includes(searchTerm))
            .slice(0, 10);
    }, [guess, steam?.allGames]);

    useEffect(() => {
        const controller = new AbortController();

        async function fetchClassicData() {
            try {
                setIsLoading(true);
                setError(null);

                const token = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                const response = await fetch("/api/classic", {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                        ...(token ? { "X-CSRF-TOKEN": token } : {}),
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error(
                        `Failed to load game data (status ${response.status})`,
                    );
                }

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                setGameData(data);
                const hasSeenTutorial =
                    sessionStorage.getItem(CLASSIC_TUTORIAL_SEEN_KEY) ===
                    "true";

                if (!hasSeenTutorial) {
                    setIsModalOpen(true);
                    sessionStorage.setItem(CLASSIC_TUTORIAL_SEEN_KEY, "true");
                }
            } catch (fetchError) {
                if (fetchError.name === "AbortError") {
                    return;
                }

                console.error("Error loading classic data:", fetchError);
                setError(
                    fetchError.message ||
                        "Something went wrong while loading game data.",
                );
            } finally {
                setIsLoading(false);
            }
        }

        fetchClassicData();

        return () => {
            controller.abort();
        };
    }, []);

    /**
     * Converts playtime in minutes to hours when >= 60, otherwise returns minutes.
     * Matches Dashboard playtime display logic.
     */
    const playtimeConversion = (playtimeInMinutes) => {
        if (playtimeInMinutes >= 60) {
            return Math.floor(playtimeInMinutes / 60);
        }
        return playtimeInMinutes;
    };

    // Normalize hints_data into an array of card configs
    const hintCards = useMemo(() => {
        if (!gameData || !gameData.hints_data) return [];

        const entries = Object.entries(gameData.hints_data);

        if (entries.length !== 9) {
            console.warn(
                `[Classic] Expected 9 hint datasets, received ${entries.length}. Rendering all available hints.`,
            );
        }

        return entries.map(([key, value]) => {
            const hintName = value?.hint_name || key;
            const data = value?.data || {};

            const rows = Object.entries(data).map(([dataKey, dataValue]) => {
                const isTotalPlaytimeCard =
                    key === "total_playtime" && dataKey === "playtime";
                const displayValue = isTotalPlaytimeCard
                    ? (() => {
                          const minutes = Number(dataValue);
                          const converted = playtimeConversion(minutes);
                          const unit = minutes >= 60 ? "hours" : "minutes";
                          return `${converted} ${unit}`;
                      })()
                    : String(dataValue);

                return {
                    label: dataKey
                        .replace(/_/g, " ")
                        .replace(/\b\w/g, (c) => c.toUpperCase()),
                    value: displayValue,
                };
            });

            return {
                id: key,
                title: hintName
                    .replace(/_/g, " ")
                    .replace(/\b\w/g, (c) => c.toUpperCase()),
                rows,
            };
        });
    }, [gameData]);

    // Helper to get a random subset of indices
    const getRandomSubset = (total, count) => {
        const indices = Array.from({ length: total }, (_, index) => index);

        for (let i = indices.length - 1; i > 0; i -= 1) {
            const j = Math.floor(Math.random() * (i + 1));
            [indices[i], indices[j]] = [indices[j], indices[i]];
        }

        return indices.slice(0, Math.min(count, total));
    };

    // Initialize obscured indices once we know how many hint cards we have
    useEffect(() => {
        if (hintCards.length === 0) return;

        const initialObscured = getRandomSubset(hintCards.length, 6);
        setObscuredIndices(initialObscured);
        setAttemptCount(0);
        setResult(null);
        setStatusMessage("");
        setIsOver(false);
    }, [hintCards.length]);

    const handleIncorrectGuess = () => {
        setAttemptCount((prev) => {
            const next = prev + 1;

            if (next === 1) {
                // First incorrect guess: keep 3 randomly obscured
                setObscuredIndices((current) => {
                    if (current.length === 0) return [];
                    const subsetIndices = getRandomSubset(current.length, 3);
                    return subsetIndices.map((i) => current[i]);
                });
                setStatusMessage("You have 2 guesses left.");
            } else if (next === 2) {
                // Second incorrect guess: reveal all
                setObscuredIndices([]);
                setStatusMessage("You have 1 guess left.");
            } else if (next >= 3) {
                // Third incorrect guess: game over
                setObscuredIndices([]);
                setIsOver(true);
                setResult("lose");
                setStatusMessage(
                    "No more guesses left. The correct game is shown below.",
                );
            }

            return next;
        });
    };

    const handleRevealMore = () => {
        if (!gameData || !gameData.game || isOver) {
            return;
        }

        handleIncorrectGuess();
    };

    const handleSubmitGuess = (event) => {
        event.preventDefault();

        if (!gameData || !gameData.game || isOver) {
            return;
        }

        const trimmedGuess = guess.trim().toLowerCase();
        const correctName = String(gameData.game.name || "")
            .trim()
            .toLowerCase();

        setIsSubmitting(true);

        if (trimmedGuess && trimmedGuess === correctName) {
            setIsSubmitting(false);
            setIsOver(true);
            setResult("win");
            setObscuredIndices([]);
            setStatusMessage("Nice! You guessed the correct game.");
            return;
        }

        setIsSubmitting(false);
        handleIncorrectGuess();
    };

    const isCardObscured = (index) => obscuredIndices.includes(index);

    return (
        <Layout isLandingPage={false} swipeOut={swipeOut} showDetails={false}>
            {isLoading ? (
                <div className="flex items-center absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
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

                    <p className="text-white text-lg">Hang on...</p>
                </div>
            ) : (
                <>
                    <div className="w-full mx-auto flex flex-col md:flex-row p-7 gap-6">
                        {isOver && gameData?.game && (
                            <Card className="w-full max-w-2xl mx-auto p-5 bg-gray-900/60 border-gray-700/70 flex flex-col sm:flex-row gap-4 items-center">
                                {gameData.game.cover_url && (
                                    <div className="w-full sm:w-48 flex-shrink-0">
                                        <img
                                            src={gameData.game.cover_url}
                                            alt={`${gameData.game.name} cover`}
                                            className="w-full rounded-xl border border-gray-700/70 object-cover"
                                        />
                                    </div>
                                )}

                                <div className="flex-1 space-y-2 text-center sm:text-left">
                                    <h2 className="text-xl font-semibold text-white">
                                        {result === "win"
                                            ? "You got it!"
                                            : "Game over"}
                                    </h2>

                                    <p className="text-gray-300">
                                        The game was{" "}
                                        <span className="font-semibold">
                                            {gameData.game.name}
                                        </span>
                                        .
                                    </p>

                                    {statusMessage && (
                                        <p className="text-sm text-gray-400">
                                            {statusMessage}
                                        </p>
                                    )}

                                    <Button
                                        type="button"
                                        ariaLabel="Play again"
                                        onClick={() => window.location.reload()}
                                    >
                                        Play again
                                    </Button>
                                </div>
                            </Card>
                        )}

                        {!isOver && hintCards.length > 0 && (
                            <Card className="p-5 bg-gray-900/50 border-gray-700/70 flex-1 min-w-0">
                                <div className="mb-4 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                                    <div>
                                        <h2 className="text-xl font-semibold text-white">
                                            Guess the game from these hints
                                        </h2>

                                        <p className="text-sm text-gray-400">
                                            Some cards are hidden at first and
                                            reveal as you guess.
                                        </p>
                                    </div>

                                    {gameData?.game?.playtime != null && (
                                        <p className="text-xs text-gray-400">
                                            Playtime:{" "}
                                            <span className="text-gray-300">
                                                {Math.round(
                                                    gameData.game.playtime / 60,
                                                )}{" "}
                                                hours
                                            </span>
                                        </p>
                                    )}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    {hintCards.map((card, index) => {
                                        const obscured = isCardObscured(index);

                                        return (
                                            <Card
                                                key={card.id}
                                                padding={6}
                                                className="relative bg-gray-900/60 border-gray-700/70 h-full overflow-hidden"
                                            >
                                                <div
                                                    className={
                                                        obscured
                                                            ? "space-y-2 blur-md select-none pointer-events-none"
                                                            : "space-y-2"
                                                    }
                                                >
                                                    <h3 className="text-sm font-semibold text-white truncate">
                                                        {card.title}
                                                    </h3>

                                                    <ul className="space-y-1">
                                                        {card.rows.map(
                                                            (row) => {
                                                                const isBlurredImageUrl =
                                                                    card.id ===
                                                                        "blurred_banner" &&
                                                                    /^https?:\/\//i.test(
                                                                        row.value,
                                                                    );
                                                                const isTagsCard =
                                                                    card.id ===
                                                                    "tags";
                                                                const tagValues =
                                                                    isTagsCard
                                                                        ? row.value
                                                                              .split(
                                                                                  /,\s*/,
                                                                              )
                                                                              .filter(
                                                                                  Boolean,
                                                                              )
                                                                        : [];

                                                                return (
                                                                    <li
                                                                        key={
                                                                            row.label
                                                                        }
                                                                        className="text-xs text-gray-300"
                                                                    >
                                                                        {isBlurredImageUrl ? (
                                                                            <div
                                                                                className="mt-1 w-full aspect-[460/215] rounded-lg border border-gray-700/70 overflow-hidden bg-gray-800/30"
                                                                                aria-hidden="true"
                                                                            >
                                                                                {!obscured && (
                                                                                    <img
                                                                                        src={
                                                                                            row.value
                                                                                        }
                                                                                        alt="Blurred game banner"
                                                                                        className="w-full h-full object-cover blur-md"
                                                                                    />
                                                                                )}
                                                                            </div>
                                                                        ) : isTagsCard &&
                                                                          tagValues.length >
                                                                              0 ? (
                                                                            <div className="flex flex-wrap gap-1.5">
                                                                                {tagValues.map(
                                                                                    (
                                                                                        tag,
                                                                                        i,
                                                                                    ) => (
                                                                                        <span
                                                                                            key={`${tag}-${i}`}
                                                                                            className="inline-block px-2.5 py-1 rounded-md bg-gray-700/80 text-gray-200 border border-gray-600/50"
                                                                                        >
                                                                                            {
                                                                                                tag
                                                                                            }
                                                                                        </span>
                                                                                    ),
                                                                                )}
                                                                            </div>
                                                                        ) : (
                                                                            <>
                                                                                <span className="text-gray-400">
                                                                                    {
                                                                                        row.label
                                                                                    }

                                                                                    :
                                                                                </span>{" "}
                                                                                <span>
                                                                                    {
                                                                                        row.value
                                                                                    }
                                                                                </span>
                                                                            </>
                                                                        )}
                                                                    </li>
                                                                );
                                                            },
                                                        )}
                                                    </ul>
                                                </div>
                                            </Card>
                                        );
                                    })}
                                </div>
                            </Card>
                        )}

                        {/* Input Card */}
                        {!isOver && (
                            <Card className="flex flex-col justify-center space-y-6 w-full md:w-auto md:flex-none md:max-w-md">
                                <div className="text-center space-y-2">
                                    <h3 className="text-2xl font-semibold text-white">
                                        Enter Your Guess
                                    </h3>

                                    {statusMessage && !isOver && (
                                        <p className="text-sm text-gray-400">
                                            {statusMessage}
                                        </p>
                                    )}
                                </div>

                                <form
                                    className="flex flex-col gap-5"
                                    autoComplete="off"
                                    onSubmit={handleSubmitGuess}
                                >
                                    <div className="relative w-full">
                                        <input
                                            className="w-full px-4 py-3 bg-gray-800/50 border rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-1 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed border-gray-700 focus:ring-blue-500 focus:border-transparent"
                                            id="guess-input"
                                            type="text"
                                            value={guess}
                                            required
                                            autoComplete="off"
                                            placeholder="Enter game name..."
                                            onChange={(event) => {
                                                setGuess(event.target.value);
                                                setShowSuggestions(true);
                                                setSelectedIndex(-1);
                                            }}
                                            onFocus={() => {
                                                if (filteredGames.length > 0) {
                                                    setShowSuggestions(true);
                                                }
                                            }}
                                            onBlur={() => {
                                                // Delay to allow click events to fire
                                                setTimeout(
                                                    () =>
                                                        setShowSuggestions(
                                                            false,
                                                        ),
                                                    200,
                                                );
                                            }}
                                            onKeyDown={(e) => {
                                                // This code was made with the help of AI
                                                if (filteredGames.length === 0)
                                                    return;

                                                if (e.key === "ArrowDown") {
                                                    e.preventDefault();
                                                    setSelectedIndex((prev) =>
                                                        prev <
                                                        filteredGames.length - 1
                                                            ? prev + 1
                                                            : prev,
                                                    );
                                                } else if (
                                                    e.key === "ArrowUp"
                                                ) {
                                                    e.preventDefault();
                                                    setSelectedIndex((prev) =>
                                                        prev > 0
                                                            ? prev - 1
                                                            : -1,
                                                    );
                                                } else if (
                                                    e.key === "Enter" &&
                                                    selectedIndex >= 0
                                                ) {
                                                    e.preventDefault();
                                                    setGuess(
                                                        filteredGames[
                                                            selectedIndex
                                                        ].name,
                                                    );
                                                    setShowSuggestions(false);
                                                    setSelectedIndex(-1);
                                                } else if (
                                                    e.key === "Tab" &&
                                                    selectedIndex >= 0
                                                ) {
                                                    e.preventDefault();
                                                    setGuess(
                                                        filteredGames[
                                                            selectedIndex
                                                        ].name,
                                                    );
                                                    setShowSuggestions(false);
                                                    setSelectedIndex(-1);
                                                } else if (e.key === "Escape") {
                                                    setShowSuggestions(false);
                                                    setSelectedIndex(-1);
                                                }
                                            }}
                                            disabled={isSubmitting || isOver}
                                        />
                                        {showSuggestions &&
                                            filteredGames.length > 0 && (
                                                <div className="absolute z-10 w-full mt-1 bg-gray-800/95 backdrop-blur-sm border border-gray-700 rounded-lg shadow-2xl max-h-60 overflow-y-auto">
                                                    {filteredGames.map(
                                                        (game, index) => (
                                                            <button
                                                                key={game.id}
                                                                type="button"
                                                                className={`w-full text-left px-4 py-2 hover:bg-gray-700/50 transition-colors ${
                                                                    index ===
                                                                    selectedIndex
                                                                        ? "bg-gray-700/50"
                                                                        : ""
                                                                }`}
                                                                onClick={() => {
                                                                    setGuess(
                                                                        game.name,
                                                                    );
                                                                    setShowSuggestions(
                                                                        false,
                                                                    );
                                                                    setSelectedIndex(
                                                                        -1,
                                                                    );
                                                                }}
                                                                onMouseEnter={() =>
                                                                    setSelectedIndex(
                                                                        index,
                                                                    )
                                                                }
                                                            >
                                                                <span className="text-white">
                                                                    {game.name}
                                                                </span>
                                                            </button>
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                    </div>

                                    {error && (
                                        <div className="p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-red-400 text-sm">
                                            {error}
                                        </div>
                                    )}

                                    <Button
                                        type="submit"
                                        disabled={isSubmitting || isOver}
                                        ariaLabel="Submit Guess"
                                        className="w-1/2"
                                    >
                                        {isSubmitting ? (
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
                                                        d="M4 12a 8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                Hold on...
                                            </span>
                                        ) : (
                                            "Submit Guess"
                                        )}
                                    </Button>

                                    <Button
                                        type="button"
                                        disabled={isOver || attemptCount >= 2}
                                        ariaLabel="Reveal more"
                                        isGreyVariant={true}
                                        onClick={handleRevealMore}
                                    >
                                        Reveal more
                                    </Button>
                                </form>
                            </Card>
                        )}
                    </div>

                    {/* Tutorial Modal */}
                    <Modal
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        title="Welcome to SteamGuessr Classic!"
                    >
                        <ul className="space-y-3">
                            <li className="text-xl font-semibold text-white">
                                How does this work?
                            </li>

                            <li>
                                • Guess the correct game based on the steam
                                store data provided.
                            </li>

                            <li>
                                • You have <strong>3 guesses</strong> to guess
                                the correct game. After each incorrect guess,
                                more data will be shown.
                            </li>

                            <li>
                                • If you guess the correct game or run out of
                                guesses, the correct answer and all data will be
                                shown.
                            </li>
                        </ul>
                    </Modal>
                </>
            )}
        </Layout>
    );
}
