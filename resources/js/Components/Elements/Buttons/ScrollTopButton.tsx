import { ArrowUp } from "lucide-react";
import { useEffect, useState } from "react";

const ScrollTopButton = () => {
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        const toggleVisibility = () => {
            if (window.scrollY > 500) {
                setIsVisible(true);
            } else {
                setIsVisible(false);
            }
        };

        window.addEventListener("scroll", toggleVisibility);

        return () => {
            window.removeEventListener("scroll", toggleVisibility);
        };
    }, []);

    const scrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    };

    return (
        <>
            <div
                className={`fixed cursor-pointer bottom-5 right-5 z-50 transition-opacity duration-500 ${
                    isVisible ? "opacity-100" : "opacity-0 pointer-events-none"
                }`}
                onClick={scrollToTop}
            >
                <div className="bg-fg hover:bg-link-hover text-bg border-2 border-bg w-10 sm:w-12 h-10 sm:h-12 flex justify-center items-center rounded-full animate-bounce hover:animate-none group">
                    <ArrowUp className="w-6 h-6 font-extrabold " />
                </div>
            </div>
        </>
    );
};

export default ScrollTopButton;
