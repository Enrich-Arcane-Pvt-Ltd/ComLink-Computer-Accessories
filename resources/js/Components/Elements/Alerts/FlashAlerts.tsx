import SuccessAlert from "./SuccessAlerts";
import { PageProps } from "@/types";
import DangerAlert from "./DangerAlerts";

export default function FlashAlerts({ flash }: { flash: PageProps["flash"] }) {
    let isSuccessArray = Array.isArray(flash?.success) ? true : false;
    let isErrorArray = Array.isArray(flash?.error) ? true : false;
    return (
        <>
            {flash?.success && (
                <SuccessAlert
                    key={isSuccessArray ? flash?.success[1] : flash?.success}
                    title="Success"
                    message={
                        isSuccessArray ? flash?.success[0] : flash?.success
                    }
                />
            )}

            {flash?.error && (
                <DangerAlert
                    key={isErrorArray ? flash?.error[1] : flash?.error}
                    title="Error"
                    message={isErrorArray ? flash?.error[0] : flash?.error}
                />
            )}
        </>
    );
}
