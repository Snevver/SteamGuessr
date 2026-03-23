import React, { useEffect, useState } from "react";
import { router, usePage } from "@inertiajs/react";
import CountUp from "react-countup";
import { ArrowUpDown, ALargeSmall, Info } from "lucide-react";
import Layout from "../Layouts/Layout";
import Card from "../Components/Card";
import MinigameCard from "../Components/MinigameCard";

export default function Dashboard() {
    const { steam } = usePage().props;
    const [swipeOut, setSwipeOut] = useState(false);

    // !!! Debugging. Remove before deployment.
    useEffect(() => {
        console.log("steam data:", steam);
    }, [steam]);

    /**
     * Converts the playtime in minutes to hours if needed.
     * @param {number} playtimeInMinutes - The playtime in minutes.
     * @returns {number} The playtime in hours if needed, otherwise the playtime in minutes.
     */
    const playtimeConversion = (playtimeInMinutes) => {
        if (playtimeInMinutes >= 60) {
            return Math.floor(playtimeInMinutes / 60);
        } else {
            return playtimeInMinutes;
        }
    };

    // Color the persona state badge
    const personaStateClasses = {
        Offline: "bg-gray-500/10 text-gray-300 border-gray-500/30",
        Online: "bg-green-500/10 text-green-300 border-green-500/30",
        Busy: "bg-red-500/10 text-red-300 border-red-500/30",
        Away: "bg-yellow-500/10 text-yellow-300 border-yellow-500/30",
    };

    const personaStateBadgeClass =
        personaStateClasses[steam.personaState] ||
        "bg-blue-500/10 text-blue-300 border-blue-500/30";

    const handleMinigameNavigation = (href) => {
        setSwipeOut(true);

        setTimeout(() => {
            router.visit(href);
        }, 300);
    };

    return (
        <Layout swipeOut={swipeOut}>
            <Card className="flex flex-col w-full max-w-4xl mx-auto gap-8">
                {/* Profile header */}
                <div className="relative flex flex-col sm:flex-row justify-center items-center gap-6">
                    <div className="absolute top-0 right-0 flex items-center gap-1 text-xs sm:text-sm text-gray-400">
                        <Info className="w-4 h-4" />

                        <span className="hidden sm:inline">
                            Scroll down to discover the minigames
                        </span>

                        <span className="w-[60px] sm:hidden">Scroll for minigames</span>
                    </div>
                    <div className="relative">
                        <img
                            src={steam.profilePictureURL}
                            alt="Steam Profile Picture"
                            className="w-24 h-24 rounded-2xl border border-gray-700 shadow-lg object-cover"
                        />
                    </div>

                    <div className="space-y-1">
                        <h1 className="text-2xl text-center sm:text-left font-semibold text-white">
                            {steam.username}
                        </h1>

                        <p className="text-sm text-center sm:text-left text-gray-400 font-mono break-all">
                            Steam ID:{" "}
                            <span className="text-gray-300">
                                {steam.steamID}
                            </span>
                        </p>

                        <div className="flex justify-center sm:justify-start flex-wrap gap-2 mt-2">
                            <span
                                className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border ${personaStateBadgeClass}`}
                            >
                                {steam.personaState}
                            </span>

                            <span className="inline-flex text-center items-center px-2.5 py-1 rounded-full text-xs font-medium bg-purple-500/10 text-purple-300 border border-purple-500/30">
                                Created on {steam.timeCreated || "Unknown"}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Stats grid */}
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total games
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={steam.totalGamesOwned}
                                duration={1.5}
                                start={0}
                            />
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Total playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={playtimeConversion(
                                    steam.totalPlaytimeMinutes
                                )}
                                duration={1.5}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                {playtimeConversion(
                                    steam.totalPlaytimeMinutes
                                ) > 1
                                    ? "hours"
                                    : "minutes"}
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Average playtime
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={playtimeConversion(
                                    steam.averagePlaytimeMinutes
                                )}
                                duration={1.5}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                {playtimeConversion(
                                    steam.averagePlaytimeMinutes
                                ) > 1
                                    ? "hours"
                                    : "minutes"}
                            </span>
                        </p>
                    </Card>

                    <Card className="p-5 bg-gray-900/50 border-gray-700/70">
                        <p className="text-xs uppercase tracking-wide text-gray-400">
                            Games played
                        </p>

                        <p className="mt-2 text-2xl font-semibold text-white">
                            <CountUp
                                end={steam.playedPercentage}
                                decimals={2}
                                duration={1.5}
                                start={0}
                            />

                            <span className="ml-1 text-sm text-gray-400">
                                %
                            </span>
                        </p>
                    </Card>
                </div>
            </Card>

            {/* Top games */}
            <Card className="flex flex-col w-full max-w-4xl mx-auto gap-6">
                <div className="flex flex-col items-center">
                    <h2 className="text-xl sm:text-3xl font-semibold text-white">
                        Your top games
                    </h2>

                    <p className="text-xs sm:text-sm text-gray-400 mt-1">
                        Based on total playtime across your Steam library
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    {steam.topGames.slice(0, 3).map((game, index) => {
                        const totalPlayTime = playtimeConversion(
                            game.playtime_forever
                        );

                        return (
                            <Card
                                className="relative bg-gray-900/50 border-gray-700/70 hover:bg-gray-900/70 hover:cursor-pointer hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
                                onClick={() =>
                                    window.open(
                                        `https://store.steampowered.com/app/${game.appid}`,
                                        "_blank"
                                    )
                                }
                                padding={5}
                                key={game.appid}
                            >
                                <div className="absolute top-2 left-2 w-8 h-8 flex items-center justify-center z-30 text-sm text-gray-400 bg-gray-900/50 border border-gray-700/70 rounded-full">
                                    #{index + 1}
                                </div>

                                <div className="relative w-full aspect-[16/9] overflow-hidden">
                                    <img
                                        src={game.cover_url}
                                        alt={`${game.name} cover`}
                                        className="w-full h-full rounded-xl object-cover"
                                    />
                                </div>

                                <div className="p-3 pb-0 sm:p-4 sm:pb-0 flex flex-col gap-1">
                                    <p className="text-lg font-semibold text-white text-center">
                                        {game.name}
                                    </p>

                                    <p className="text-sm text-gray-400 text-center">
                                        <CountUp
                                            end={totalPlayTime}
                                            duration={1.5}
                                            start={0}
                                        />{" "}
                                        {totalPlayTime === 1
                                            ? "hour played"
                                            : "hours played"}
                                    </p>
                                </div>
                            </Card>
                        );
                    })}
                </div>
            </Card>

            {/* Minigame grid */}
            <Card className="flex flex-col w-full max-w-4xl mx-auto gap-6">
                <div className="flex flex-col items-center">
                    <h2 className="text-xl sm:text-3xl font-semibold text-white">
                        Minigames
                    </h2>

                    <p className="text-xs sm:text-sm text-gray-400 mt-1">
                        Choose which SteamGuessr minigame you want to play
                    </p>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <MinigameCard
                        useLogo={true}
                        title="SteamGuessr Classic"
                        description="Guess the game from its statistics"
                        href="/minigame/classic"
                        onNavigate={handleMinigameNavigation}
                    />
                    <MinigameCard
                        icon={ArrowUpDown}
                        title="Higher or Lower"
                        description="Guess which game statistic is higher"
                        disabled={true}
                    />
                    <MinigameCard
                        icon={ALargeSmall}
                        title="Game Wordle"
                        description="Wordle, but with games"
                        disabled={true}
                    />
                </div>
            </Card>
        </Layout>
    );
}
