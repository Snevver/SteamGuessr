/**
 * A simple button component.
 * @param {React.ReactNode} children - The children to render inside the button.
 * @param {string} className - The classes to apply to the button. Overwrites the default classes.
 * @param {string} ariaLabel - The aria label of the button.
 * @param {function} onClick - The function to call when the button is clicked.
 * @param {boolean} disabled - Whether the button is disabled.
 * @param {string} type - The type of the button.
 * @param {object} props - The props to pass to the button. Overwrites the default props.
 * @returns {React.ReactNode} - The button component.
 */
export default function Button({
    children,
    className = "",
    ariaLabel = "Button",
    onClick = () => {},
    disabled = false,
    type = "button",
    isGreyVariant = false,
    ...props
}) {
    return (
        <button
            onClick={onClick}
            aria-label={ariaLabel}
            disabled={disabled}
            type={type}
            className={`w-full py-3 ${
                isGreyVariant
                    ? "bg-gray-700/50 hover:bg-gray-700/70 shadow-gray-500/25"
                    : "bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 shadow-blue-500/25"
            } text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}
