<?php

declare(strict_types=1);

namespace JouvencePara\Core\Geography;

final class TunisiaGeography
{
    /** @return list<GeographyNode> */
    public static function governorates(): array
    {
        $labels = [
            'tn-ariana' => 'Ariana', 'tn-beja' => 'Béja', 'tn-ben-arous' => 'Ben Arous', 'tn-bizerte' => 'Bizerte',
            'tn-gabes' => 'Gabès', 'tn-gafsa' => 'Gafsa', 'tn-jendouba' => 'Jendouba', 'tn-kairouan' => 'Kairouan',
            'tn-kasserine' => 'Kasserine', 'tn-kebili' => 'Kébili', 'tn-kef' => 'Le Kef', 'tn-mahdia' => 'Mahdia',
            'tn-manouba' => 'La Manouba', 'tn-medenine' => 'Médenine', 'tn-monastir' => 'Monastir', 'tn-nabeul' => 'Nabeul',
            'tn-sfax' => 'Sfax', 'tn-sidi-bouzid' => 'Sidi Bouzid', 'tn-siliana' => 'Siliana', 'tn-sousse' => 'Sousse',
            'tn-tataouine' => 'Tataouine', 'tn-tozeur' => 'Tozeur', 'tn-tunis' => 'Tunis', 'tn-zaghouan' => 'Zaghouan',
        ];
        return array_map(
            static fn (string $id, string $label): GeographyNode => new GeographyNode($id, GeographyNode::GOVERNORATE, $label),
            array_keys($labels),
            array_values($labels)
        );
    }
}
