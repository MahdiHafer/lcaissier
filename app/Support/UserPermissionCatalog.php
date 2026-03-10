<?php

namespace App\Support;

use Illuminate\Support\Str;

class UserPermissionCatalog
{
    public static function sections(): array
    {
        return [
            'Pilotage' => [
                'dashboard.view' => 'Dashboard',
            ],
            'Vente' => [
                'caisse.use' => 'Caisse',
                'documents.ventes.view' => 'Historique ventes',
            ],
            'Catalogue' => [
                'catalog.products.view' => 'Produits',
                'catalog.stock.view' => 'Stock',
                'inventory.manage' => 'Inventaires',
                'catalog.categories.manage' => 'Categories',
                'catalog.colors.manage' => 'Couleurs',
            ],
            'Tiers' => [
                'tiers.clients.view' => 'Clients',
                'tiers.fournisseurs.view' => 'Fournisseurs (liste)',
                'tiers.fournisseurs.create' => 'Fournisseurs (creation)',
            ],
            'Documents' => [
                'documents.bl.view' => 'BL',
                'documents.bl.convert' => 'BL -> vente/facture',
                'documents.devis.view' => 'Devis',
                'documents.factures.view' => 'Factures',
                'documents.avoirs.view' => 'Avoirs',
            ],
            'Parametrage' => [
                'settings.entreprise' => 'Entreprise',
                'settings.exports' => 'Exports',
                'settings.users.manage' => 'Utilisateurs',
            ],
        ];
    }

    public static function allKeys(): array
    {
        $all = [];
        foreach (self::sections() as $section) {
            $all = array_merge($all, array_keys($section));
        }
        return $all;
    }

    public static function defaultAgentPermissions(): array
    {
        return [
            'caisse.use',
            'documents.ventes.view',
            'catalog.products.view',
            'catalog.stock.view',
            'tiers.clients.view',
            'tiers.fournisseurs.create',
            'documents.bl.view',
            'documents.devis.view',
            'documents.factures.view',
            'documents.avoirs.view',
        ];
    }

    public static function requiredForRoute(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $map = [
            'dashboard.*' => 'dashboard.view',
            'caisse.*' => 'caisse.use',
            'ventes.avoir.*' => 'documents.avoirs.view',
            'ventes.*' => 'documents.ventes.view',
            'products.*' => 'catalog.products.view',
            'stock.*' => 'catalog.stock.view',
            'inventory.*' => 'inventory.manage',
            'categories.*' => 'catalog.categories.manage',
            'colors.*' => 'catalog.colors.manage',
            'clients.*' => 'tiers.clients.view',
            'fournisseurs.create' => 'tiers.fournisseurs.create',
            'fournisseurs.store' => 'tiers.fournisseurs.create',
            'fournisseurs.*' => 'tiers.fournisseurs.view',
            'bons-livraison.convert*' => 'documents.bl.convert',
            'bons-livraison.*' => 'documents.bl.view',
            'devis.*' => 'documents.devis.view',
            'factures.*' => 'documents.factures.view',
            'avoirs.*' => 'documents.avoirs.view',
            'settings.export.*' => 'settings.exports',
            'settings.*' => 'settings.entreprise',
            'users.*' => 'settings.users.manage',
        ];

        foreach ($map as $pattern => $permission) {
            if (Str::is($pattern, $routeName)) {
                return $permission;
            }
        }

        return null;
    }
}

