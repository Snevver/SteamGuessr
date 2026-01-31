import React from "react";
import { router } from "@inertiajs/react";
import Card from "./Card";
import { CircleQuestionMark, Hammer } from "lucide-react";

/**
 * A card component that displays a minigame.
 * @param {boolean} useLogo - Whether to use the logo.
 * @param {React.ReactNode} icon - The icon to display.
 * @param {string} title - The title of the minigame.
 * @param {string} description - The description of the minigame.
 * @param {boolean} disabled - Whether the minigame is disabled.
 * @param {string} href - The href of the minigame.
 * @param {function} onNavigate - The function to call when the minigame is navigated to.
 * @param {object} props - The props to pass to the card.
 * @returns {React.ReactNode} - The minigame card component.
 */
export default function MinigameCard({
    useLogo = false,
    icon = CircleQuestionMark,
    title = "Error",
    description = "Error",
    disabled = false,
    href = "/",
    onNavigate,
    ...props
}) {
    const IconComponent = icon;

    const handleClick = (event) => {
        event.preventDefault();

        if (disabled) {
            return;
        }

        if (onNavigate) {
            onNavigate(href);
        } else {
            router.visit(href);
        }
    };

    return (
        <a href={href} onClick={handleClick} aria-disabled={disabled}>
            <Card
                className={`relative flex flex-col gap-1 bg-gray-900/50 border-gray-700/70 transition-all duration-200 ${
                    disabled
                        ? "cursor-not-allowed"
                        : "cursor-pointer hover:bg-gray-900/70 hover:scale-[1.02] active:scale-[0.98]"
                }`}
                {...props}
            >
                <div
                    className={`flex items-center gap-3 ${
                        disabled ? "blur-sm" : ""
                    }`}
                >
                    {useLogo && (
                        <img
                            src="/images/logo-white.png"
                            alt={`${title} logo`}
                            className="w-6 h-6"
                        />
                    )}

                    {!useLogo && <IconComponent className="text-2xl" />}
                    <h1 className="text-2xl font-semibold text-white">
                        {title}
                    </h1>
                </div>

                <p
                    className={`text-sm text-gray-400 ${
                        disabled ? "blur-sm" : ""
                    }`}
                >
                    {description}
                </p>

                {disabled && (
                    <div className="absolute inset-0 flex flex-col items-center justify-center bg-black/60 rounded-xl z-10">
                        <Hammer className="w-4 h-4 mb-2 text-white" />
                        <p className="text-sm text-white">Coming soon...</p>
                    </div>
                )}
            </Card>
        </a>
    );
}
