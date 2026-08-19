import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import apiConfig from "../../config/apiConfig";
import { apiBaseURL } from "../../constants";
import TopProgressBar from "../../shared/components/loaders/TopProgressBar";
import MasterLayout from "../MasterLayout";
import HeaderTitle from "../header/HeaderTitle";

const intervalLabel = (interval) => {
    if (interval === "year") {
        return "par an";
    }
    if (interval === "week") {
        return "par semaine";
    }
    return "par mois";
};

const Subscriptions = () => {
    const location = useLocation();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");
    const [data, setData] = useState(null);
    const [payingId, setPayingId] = useState(null);

    const load = () => {
        setLoading(true);
        apiConfig
            .get(apiBaseURL.SUBSCRIPTION)
            .then((response) => {
                setData(response.data.data);
                setError("");
            })
            .catch((err) => {
                setError(
                    err.response?.data?.message ||
                        "Impossible de charger les abonnements."
                );
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        const params = new URLSearchParams(location.search);
        const reference = params.get("reference");
        if (params.get("paid") === "1" && reference) {
            apiConfig
                .get(`${apiBaseURL.SUBSCRIPTION_VERIFY}?reference=${reference}`)
                .finally(load);
            return;
        }
        load();
    }, [location.search]);

    const checkout = (planId) => {
        setPayingId(planId);
        setError("");
        apiConfig
            .post(apiBaseURL.SUBSCRIPTION_CHECKOUT, { plan_id: planId })
            .then((response) => {
                const payload = response.data.data;
                if (payload.activated) {
                    load();
                    return;
                }
                if (payload.checkout_url) {
                    window.location.href = payload.checkout_url;
                }
            })
            .catch((err) => {
                setError(
                    err.response?.data?.message ||
                        "Paiement impossible pour le moment."
                );
            })
            .finally(() => setPayingId(null));
    };

    const subscription = data?.subscription || {};
    const plans = data?.plans || [];
    const currentName = subscription.plan_name;

    return (
        <MasterLayout>
            <TopProgressBar />
            <HeaderTitle title="Abonnement" />
            {loading ? (
                <p>Chargement…</p>
            ) : (
                <>
                    {error ? (
                        <div className="alert alert-danger">{error}</div>
                    ) : null}
                    {data && data.payment_ready === false ? (
                        <div className="alert alert-warning">
                            GeniusPay n’est pas encore configuré (clé secrète
                            manquante). Les offres gratuites restent activables.
                        </div>
                    ) : null}
                    <div className="card mb-4">
                        <div className="card-body">
                            <h3 className="mb-3">Offre en cours</h3>
                            {subscription.active ? (
                                <>
                                    <p className="mb-1">
                                        <strong>{currentName || "—"}</strong>{" "}
                                        — {subscription.label}
                                    </p>
                                    <p className="text-muted mb-0">
                                        Temps restant :{" "}
                                        <strong>
                                            {subscription.days_left} jour
                                            {subscription.days_left > 1
                                                ? "s"
                                                : ""}
                                        </strong>
                                    </p>
                                </>
                            ) : (
                                <>
                                    <p className="text-danger mb-2">
                                        {subscription.message ||
                                            "Abonnement inactif ou expiré."}
                                    </p>
                                    <p className="mb-0 text-muted">
                                        Vous pouvez vous connecter, mais la
                                        plateforme reste en lecture limitée
                                        jusqu’au renouvellement.
                                    </p>
                                </>
                            )}
                        </div>
                    </div>
                    <h3 className="mb-3">Offres disponibles</h3>
                    <div className="row">
                        {plans.map((plan) => {
                            const isCurrent =
                                currentName && currentName === plan.name;
                            return (
                                <div
                                    className="col-md-4 mb-4"
                                    key={plan.id}
                                >
                                    <div className="card h-100">
                                        <div className="card-body d-flex flex-column">
                                            <h4>{plan.name}</h4>
                                            <p className="fs-2 fw-bold mb-1">
                                                {Number(plan.price).toLocaleString(
                                                    "fr-FR"
                                                )}{" "}
                                                {plan.currency || "XOF"}
                                            </p>
                                            <p className="text-muted">
                                                {intervalLabel(plan.interval)}
                                            </p>
                                            {plan.description ? (
                                                <p>{plan.description}</p>
                                            ) : null}
                                            <button
                                                type="button"
                                                className="btn btn-primary mt-auto"
                                                disabled={
                                                    payingId === plan.id ||
                                                    (isCurrent &&
                                                        subscription.active)
                                                }
                                                onClick={() => checkout(plan.id)}
                                            >
                                                {isCurrent &&
                                                subscription.active
                                                    ? "Offre active"
                                                    : subscription.active
                                                    ? "Changer d’offre"
                                                    : "Reconduire"}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </>
            )}
        </MasterLayout>
    );
};

export default Subscriptions;
