/**
 * A simple card component that can be used to display content in a card-like style.
 * @param {React.ReactNode} children - The children to render inside the card.
 * @param {string} className - Extra classes to apply to the card.
 * @param {number} transparency - Background transparency (must match Tailwind's allowed values).
 * @param {number} padding - Padding size, mapped to Tailwind spacing scale.
 * @returns {React.ReactNode} - The card component.
 */
export default function Card({
    children,
    className = "",
    transparency = 40,
    padding = 8,
    ...props
}) {
    const paddingClasses = {
        0: "p-0",
        1: "p-1",
        2: "p-2",
        3: "p-3",
        4: "p-4",
        5: "p-5",
        6: "p-6",
        8: "p-8",
        10: "p-10",
    };

    const opacityClasses = {
        10: "bg-gray-800/10",
        20: "bg-gray-800/20",
        30: "bg-gray-800/30",
        40: "bg-gray-800/40",
        50: "bg-gray-800/50",
        60: "bg-gray-800/60",
        70: "bg-gray-800/70",
        80: "bg-gray-800/80",
        90: "bg-gray-800/90",
        95: "bg-gray-800/95",
    };

    const paddingClass = paddingClasses[padding] ?? paddingClasses[8];
    const backgroundClass = opacityClasses[transparency] ?? opacityClasses[40];

    return (
        <div
            className={`${paddingClass} rounded-2xl ${backgroundClass} backdrop-blur-sm border border-gray-700/50 shadow-2xl ${className}`}
            {...props}
        >
            {children}
        </div>
    );
}
