<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class TaxonomyDefinition
{
    public const BRAND = 'jp_brand';
    public const CONCERN = 'jp_need';

    /** @return array<string, string> */
    public static function topLevelCategories(): array
    {
        return [
            'visage' => 'Visage',
            'corps' => 'Corps',
            'capillaire' => 'Capillaire',
            'hygiene' => 'Hygiène',
            'protection-solaire' => 'Protection solaire',
            'bebe-maman' => 'Bébé & Maman',
            'complements-alimentaires' => 'Compléments alimentaires',
            'bio-naturel' => 'Bio & Naturel',
            'homme' => 'Homme',
            'yeux' => 'Yeux',
            'mains-pieds-levres' => 'Mains, pieds et lèvres',
            'materiel-medical-orthopedique' => 'Matériel médical et orthopédique',
        ];
    }

    /** @return array<string, array{name: string, slug: string}> */
    public static function globalAttributes(): array
    {
        return [
            'skin_type' => ['name' => 'Type de peau', 'slug' => 'skin_type'],
            'hair_type' => ['name' => 'Type de cheveux', 'slug' => 'hair_type'],
            'spf' => ['name' => 'SPF', 'slug' => 'spf'],
            'size_volume' => ['name' => 'Contenance', 'slug' => 'size_volume'],
            'target_audience' => ['name' => 'Public cible', 'slug' => 'target_audience'],
        ];
    }

    public static function stableSlug(string $value): string
    {
        $value = trim($value);
        $map = [
            'À' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Ç' => 'c', 'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Î' => 'i', 'Ï' => 'i', 'Ô' => 'o', 'Ö' => 'o', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ÿ' => 'y', 'Œ' => 'oe',
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y',
            'œ' => 'oe', '&' => ' ',
        ];
        $value = strtolower(strtr($value, $map));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }
}
