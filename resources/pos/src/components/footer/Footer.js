import React from "react";
import { FeatureFooter } from "./footerText";

const Footer = (props) => {
    const { allConfigData } = props;
    return (
        <footer className="border-top w-100 pt-4 mt-7 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p className="fs-6 text-gray-600 mb-0">{FeatureFooter}</p>
            <div className="fs-6 text-gray-600">
                {allConfigData && allConfigData.is_version === "1"
                    ? "v" + allConfigData.version
                    : ""}
            </div>
        </footer>
    );
};

export default Footer;
