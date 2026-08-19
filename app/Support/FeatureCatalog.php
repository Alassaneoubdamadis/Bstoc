<?php

namespace App\Support;

class FeatureCatalog
{
    public const FOOTER = 'Créé par Alassane Oubda — Tous droits réservés. Contact : oubdaalassane01@gmail.com · +225 0757613098';

    public static function labels(): array
    {
        return [
            'manage_dashboard' => 'Tableau de bord',
            'manage_pos_screen' => 'Caisse (PDV)',
            'manage_products' => 'Produits',
            'manage_product_categories' => 'Catégories de produits',
            'manage_brands' => 'Marques',
            'manage_units' => 'Unités',
            'manage_variations' => 'Variations',
            'manage_print_barcode' => 'Codes-barres',
            'manage_adjustments' => 'Ajustements de stock',
            'manage_quotations' => 'Devis',
            'manage_purchase' => 'Achats',
            'manage_purchase_return' => 'Retours d’achat',
            'manage_sale' => 'Ventes',
            'manage_sale_return' => 'Retours de vente',
            'manage_transfers' => 'Transferts',
            'manage_expenses' => 'Dépenses',
            'manage_expense_categories' => 'Catégories de dépenses',
            'manage_customers' => 'Clients',
            'manage_suppliers' => 'Fournisseurs',
            'manage_users' => 'Créer / gérer les utilisateurs de l’entreprise',
            'manage_roles' => 'Rôles et autorisations dans l’entreprise',
            'manage_warehouses' => 'Entrepôts',
            'manage_report' => 'Rapports',
            'manage_reports' => 'Rapports (suite)',
            'manage_currency' => 'Devises',
            'manage_language' => 'Langues',
            'manage_email_templates' => 'Modèles d’e-mail',
            'manage_sms_templates' => 'Modèles SMS',
            'manage_sms_apis' => 'API SMS',
            'manage_setting' => 'Réglages (logo, nom, pied de page, etc.)',
        ];
    }

    public static function label(string $name): string
    {
        return self::labels()[$name] ?? str_replace('_', ' ', $name);
    }
}
