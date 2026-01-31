import React, { useEffect, useState } from "react";
import { ChevronLeft } from "lucide-react";

export default function Layout({
    children,
    isLandingPage = false,
    swipeOut = false,
    showDetails = true,
}) {
    const [showBackButton, setShowBackButton] = useState(false);
    const [isSwipingOut, setIsSwipingOut] = useState(false);
    const [isReferred, setIsReferred] = useState(false);

    const isAnimatingOut = swipeOut || isSwipingOut;

    /**
     * Play the swipe out animation and then send the user back to the previous page
     */
    function handleBackNavigation() {
        setIsSwipingOut(true);

        setTimeout(() => {
            if (window.location.pathname === "/dashboard") {
                window.location.href = "/#referred";
            } else {
                window.history.back();
            }
        }, 300);
    }

    // Wait 0.3 seconds before showing the back button as to not disrupt the swipe animation
    useEffect(() => {
        const timer = setTimeout(() => {
            setShowBackButton(true);
        }, 300);

        return () => clearTimeout(timer);
    }, []);

    // Play a different animation if the user was referred
    useEffect(() => {
        if (window.location.hash === "#referred") {
            setIsReferred(true);
        }
    }, []);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100 overflow-x-hidden overflow-y-hidden">
            {
                /* Back button */
                showBackButton && !isLandingPage && (
                    <button
                        className="fixed top-4 left-4 text-gray-300 hover:scale-110 active:scale-95 transition-all duration-200 z-30 animate-pop-in"
                        onClick={() => handleBackNavigation()}
                        aria-label="Go back"
                    >
                        <ChevronLeft />
                    </button>
                )
            }

            {/* Background decoration */}
            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-0 left-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl"></div>
                <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
            </div>

            <div className="flex flex-col justify-between min-h-screen relative z-10">
                {/* Header */}
                {showDetails && (
                    <header
                        className={`text-center pt-16 pb-8 px-4 ${
                            isLandingPage && !isReferred
                                ? "animate-fade-in"
                                : ""
                        }`}
                    >
                        <h1 className="text-5xl sm:text-6xl md:text-7xl font-bold mb-4 bg-gradient-to-r from-blue-400 via-purple-400 to-blue-400 bg-clip-text text-transparent animate-title-gradient">
                            SteamGuessr
                        </h1>
                        <h2 className="text-lg sm:text-xl md:text-2xl text-gray-300 font-light">
                            Play fun minigames based on your Steam library
                        </h2>
                    </header>
                )}

                {/* Main Content */}
                <main
                    className={`flex flex-col gap-8 items-center justify-center w-full px-4 py-8 ${
                        isAnimatingOut
                            ? "animate-swipe-out"
                            : isLandingPage
                            ? ""
                            : "animate-swipe-in"
                    }`}
                >
                    {children}
                </main>

                {/* Footer */}
                {showDetails && (
                    <footer
                        className={`py-8 px-4 text-center text-gray-500 text-sm ${
                            isAnimatingOut
                                ? "animate-swipe-out"
                                : isLandingPage && !isReferred
                                ? "animate-fade-in"
                                : "animate-swipe-in"
                        }`}
                    >
                        © 2025{" "}
                        <a
                            href="https://github.com/Snevver"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hover:underline"
                        >
                            Sven Hoeksema
                        </a>{" "}
                        &{" "}
                        <a
                            href="https://github.com/Penguin-09"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hover:underline"
                        >
                            Son Bram van der Burg
                        </a>
                        . All rights reserved.
                    </footer>
                )}
            </div>
        </div>
    );
}
