import { PageProps } from "@/types";
import { Head, Link } from "@inertiajs/react";

export default function ErrorPage({
    auth,
    status,
    message,
    statusText,
}: {
    auth: PageProps;
    status: number;
    message: string;
    statusText: string;
}) {
    return (
        <>
            <Head title={"Error - " + status} />

            <div className="relative h-full  isolate min-h-[80vh]">
                <div className="flex-wrap items-center justify-center ">
                    <div className="flex flex-col-reverse items-center justify-center gap-16 px-4 py-24 md:gap-28 md:py-20 md:px-44 lg:flex-row lg:px-24 lg:py-24">
                        <div className="relative w-full pb-12 lg:pb-0 xl:w-1/2 xl:pt-24">
                            <div className="relative">
                                <div className="">
                                    <h1 className="my-2 text-5xl font-bold text-primary">
                                        <strong>Oops! </strong>
                                    </h1>
                                    <h1 className="my-2 text-2xl font-bold text-black">
                                        <strong>
                                            {" "}
                                            {status
                                                ? `An error ${status} ${statusText}`
                                                : "An error occurred on client"}
                                            {", "}
                                        </strong>
                                        {message}
                                    </h1>
                                    <div className="my-2 text-black "></div>
                                    <p className="my-2 text-black ">
                                        Sorry about that! Please visit our home
                                        page to get where you need to go.
                                    </p>
                                    <Link href="/">
                                        <a>
                                            <button className="flex items-center px-8 py-4 my-2 mt-5 space-x-2 text-lg font-medium text-center text-black rounded hover:bg-primary bg-primary focus:outline-none focus:ring-2 focus:ring-indigo-700 focus:ring-opacity-50 sm:w-full lg:w-auto">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    className="w-5 h-5"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z"
                                                        clip-rule="evenodd"
                                                    ></path>
                                                </svg>{" "}
                                                <div>Back to Home</div>
                                            </button>
                                        </a>
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div className="relative flex h-44 w-64  flex-shrink-0 xl:h-60 xl:w-80 ">
                            <img
                                width={500}
                                height={600}
                                src="/assets/images/other/no-access.png"
                                alt="error icon"
                            />
                        </div>
                    </div>
                    <div className=" container mx-auto">
                        <div className="flex items-center justify-center text-center">
                            <Link href="/" className=" -mt-6 w-48 xl:w-60">
                                <a>
                                    <span className="sr-only">
                                        Enrich Arcane
                                    </span>
                                    <img
                                        width={120}
                                        height={80}
                                        src="/images/Comlink_logo.png"
                                        alt="ComLink"
                                        className="object-contain"
                                    />
                                </a>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
