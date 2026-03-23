import React, { useEffect } from "react";
import Card from "./Card";
import Button from "./Button";

/**
 * A reusable modal component that displays content in a centered overlay.
 * @param {boolean} isOpen - Whether the modal is open.
 * @param {function} onClose - Function to call when the modal should be closed.
 * @param {string} title - The title of the modal.
 * @param {React.ReactNode} children - The content to display inside the modal.
 * @param {string} className - Additional classes to apply to the modal card.
 * @returns {React.ReactNode} - The modal component.
 */
export default function Modal({
    isOpen,
    onClose,
    title,
    children,
    className = "",
    ...props
}) {
    // Handles ESC key press to close the modal and prevents body scroll when modal is open.
    useEffect(() => {
        if (isOpen) {
            // Prevent body scroll when modal is open
            document.body.style.overflow = "hidden";

            // Handle ESC key press
            const handleEscape = (event) => {
                if (event.key === "Escape") {
                    onClose();
                }
            };

            document.addEventListener("keydown", handleEscape);

            return () => {
                document.body.style.overflow = "";
                document.removeEventListener("keydown", handleEscape);
            };
        }
    }, [isOpen, onClose]);

    if (!isOpen) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black/50 backdrop-blur-sm"
                aria-hidden="true"
                onClick={onClose}
            ></div>

            <Card
                role="dialog"
                aria-modal="true"
                aria-labelledby={title ? "modal-title" : undefined}
                className={`flex flex-col gap-5 w-full max-w-2xl max-h-[90vh] overflow-y-auto break-words animate-fade-in relative z-10 ${className}`}
                transparency={95}
                onClick={(e) => {
                    // Prevent modal from closing when clicking inside the card
                    e.stopPropagation();
                }}
                {...props}
            >
                {title && (
                    <h3
                        id="modal-title"
                        className="text-2xl font-semibold text-center text-white"
                    >
                        {title}
                    </h3>
                )}

                {children}

                <Button onClick={onClose} ariaLabel="Close modal">
                    Got it!
                </Button>
            </Card>
        </div>
    );
}
