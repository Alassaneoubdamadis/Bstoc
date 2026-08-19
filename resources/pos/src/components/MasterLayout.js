import React, { useEffect, useState } from "react";
import { connect } from "react-redux";
import { Link, useLocation } from "react-router-dom";
import AsideDefault from "./sidebar/asideDefault";
import Header from "./header/Header";
import Footer from "./footer/Footer";
import AsideTopSubMenuItem from "./sidebar/asideTopSubMenuItem";
import { Tokens } from "../constants";
import asideConfig from "../config/asideConfig";
import { environment } from "../config/environment";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faBars } from "@fortawesome/free-solid-svg-icons";
import { fetchConfig } from "../store/action/configAction";

const MasterLayout = (props) => {
    const {
        children,
        newPermissions,
        frontSetting,
        fetchConfig,
        config,
        allConfigData,
    } = props;
    const [isResponsiveMenu, setIsResponsiveMenu] = useState(false);
    const [isMenuCollapse, setIsMenuCollapse] = useState(false);
    const newRoutes = config && prepareRoutes(config);
    const token = localStorage.getItem(Tokens.ADMIN);
    const location = useLocation();
    const subscriptionLocked = allConfigData?.subscription_active === false;
    const onSubscriptionPage = location.pathname.includes("/abonnement");

    useEffect(() => {
        if (token) {
            fetchConfig();
        }
        if (!token) {
            window.location.href = environment.URL + "#" + "/login";
        }
    }, []);

    useEffect(() => {
        if (allConfigData?.platform_name) {
            document.title = allConfigData.platform_name;
        }
        if (allConfigData?.platform_favicon) {
            let link = document.querySelector("link[rel='icon']");
            if (!link) {
                link = document.createElement("link");
                link.rel = "icon";
                document.head.appendChild(link);
            }
            link.href = allConfigData.platform_favicon;
        }
    }, [allConfigData?.platform_name, allConfigData?.platform_favicon]);

    const menuClick = () => {
        setIsResponsiveMenu(!isResponsiveMenu);
    };

    const menuIconClick = () => {
        setIsMenuCollapse(!isMenuCollapse);
    };

    return (
        <div className="d-flex flex-row flex-column-fluid position-relative">
            <AsideDefault
                asideConfig={newRoutes}
                frontSetting={frontSetting}
                isResponsiveMenu={isResponsiveMenu}
                menuClick={menuClick}
                menuIconClick={menuIconClick}
                isMenuCollapse={isMenuCollapse}
            />
            <div
                className={`${
                    isMenuCollapse === true ? "wrapper-res" : "wrapper"
                } d-flex flex-column flex-row-fluid position-relative`}
            >
                <div className="d-flex align-items-stretch justify-content-between header">
                    <div className="container-fluid d-flex align-items-stretch justify-content-xxl-between flex-grow-1">
                        <button
                            type="button"
                            className="btn d-flex align-items-center d-xl-none px-0"
                            title="Show aside menu"
                            onClick={menuClick}
                        >
                            <FontAwesomeIcon icon={faBars} className="fs-1" />
                        </button>
                        <AsideTopSubMenuItem
                            asideConfig={asideConfig}
                            isMenuCollapse={isMenuCollapse}
                        />
                        <Header newRoutes={newRoutes} />
                    </div>
                </div>
                <div
                    className="content d-flex flex-column flex-column-fluid pt-7"
                    style={
                        subscriptionLocked && !onSubscriptionPage
                            ? { filter: "grayscale(1)", pointerEvents: "none" }
                            : undefined
                    }
                >
                    <div className="d-flex flex-column-fluid">
                        <div className="container-fluid">{children}</div>
                    </div>
                </div>
                <div className="container-fluid">
                    <Footer
                        allConfigData={allConfigData}
                        frontSetting={frontSetting}
                    />
                </div>
                {subscriptionLocked && !onSubscriptionPage ? (
                    <div
                        className="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                        style={{
                            background: "rgba(255,255,255,.78)",
                            zIndex: 20,
                            padding: 24,
                        }}
                    >
                        <div
                            className="bg-white border rounded-3 shadow p-4 text-center"
                            style={{ maxWidth: 480 }}
                        >
                            <h2 className="mb-3">
                                Abonnement inactif ou expiré
                            </h2>
                            <p className="text-muted">
                                {allConfigData?.subscription?.message ||
                                    "Vous pouvez rester connecté, mais les actions sont bloquées jusqu’au renouvellement."}
                            </p>
                            <Link
                                to="/app/abonnement"
                                className="btn btn-primary"
                            >
                                Reconduire
                            </Link>
                        </div>
                    </div>
                ) : null}
            </div>
        </div>
    );
};

const getRouteWithSubMenu = (route, permissions) => {
    const subRoutes = route.subMenu
        ? route.subMenu.filter(
              (item) =>
                  permissions.indexOf(item.permission) !== -1 ||
                  item.permission === ""
          )
        : null;
    const newSubRoutes = subRoutes ? { ...route, newRoute: subRoutes } : route;
    return newSubRoutes;
};

const prepareRoutes = (config) => {
    const permissions = config;
    let filterRoutes = [];
    asideConfig.forEach((route) => {
        const permissionsRoute = getRouteWithSubMenu(route, permissions);
        if (
            (permissions && permissions.indexOf(route.permission) !== -1) ||
            route.permission === "" ||
            permissionsRoute.newRoute?.length
        ) {
            filterRoutes.push(permissionsRoute);
        }
    });
    return filterRoutes;
};

const mapStateToProps = (state) => {
    const newPermissions = [];
    const { permissions, settings, frontSetting, config, allConfigData } =
        state;

    if (permissions) {
        permissions.forEach((permission) =>
            newPermissions.push(permission.attributes.name)
        );
    }
    return { newPermissions, settings, frontSetting, config, allConfigData };
};

export default connect(mapStateToProps, { fetchConfig })(MasterLayout);
