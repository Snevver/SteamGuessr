/**
 * A skeleton loading component that displays a placeholder while content is loading.
 * @param {string} className - Additional classes to apply to the skeleton.
 * @param {string} variant - The variant of skeleton: 'text', 'circle', 'rectangle'. Default is 'text'.
 * @returns {React.ReactNode} - The skeleton component.
 */
export default function Skeleton({
    className = "",
    variant = "text",
    ...props
}) {
    const baseClasses = "animate-pulse bg-gray-700/50";

    switch (variant) {
        case "text":
            return (
                <div
                    className={`${baseClasses} ${className} h-4 rounded`}
                    {...props}
                />
            );
        case "circle":
            return (
                <div
                    className={`${baseClasses} ${className} w-20 h-20 rounded-full`}
                    {...props}
                />
            );
        case "rectangle":
            return (
                <div
                    className={`${baseClasses} ${className} rounded`}
                    {...props}
                />
            );
        default:
            return null;
    }
}
