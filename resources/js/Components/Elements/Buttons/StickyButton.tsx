import { useEffect, useState } from "react";

const StickyButton = () => {
    const [showText, setShowText] = useState(false);

    useEffect(() => {
        window.addEventListener("resize", () => {
            if (window.innerWidth < 768) {
                setShowText(true);
            }
        });
    }, []);

    const whatsappUrl = import.meta.env.VITE_WHATSAPP_URL || "";
    return (
        <>
            <div className="fixed cursor-pointer bottom-5 right-5">
                <a href={whatsappUrl} target="_blank" rel="noopener noreferrer">
                    <div className="relative flex items-center justify-center">
                        {showText && (
                            <div className="absolute w-[100px] text-center px-2 py-1 text-xs text-white transform -translate-x-1/2 -translate-y-1/2 bg-[#1EB356] rounded-full shadow-lg top-1/2 right-1/2 animate-pulse">
                                <span className="font-bold">Whatsapp Us</span>
                            </div>
                        )}
                        <div
                            className="flex items-center justify-center w-16 h-16 bg-[#25d366] rounded-full hover:bg-[#1EB356] hover:shadow-lg"
                            style={{ boxShadow: "0 0 10px rgba(0,0,0,0.3)" }}
                            onMouseEnter={() => {
                                setShowText(true);
                            }}
                            onMouseLeave={() => {
                                setShowText(false);
                            }}
                        >
                            <img
                                src="/assets/images/other/whatsapp.png"
                                alt="whatsapp"
                                className="w-12 h-12"
                            />
                        </div>
                    </div>
                </a>
            </div>
        </>
    );
};

export default StickyButton;
