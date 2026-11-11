<?php

namespace Database\Seeders;

use App\Models\Step;
use Illuminate\Database\Seeder;

class StepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            [
                'id' => 1,
                'name' => 'Étape 1 : Gare de Lens',
                'lat' => 50.4268,
                'lng' => 2.8281,
                'target_radius' => 30,
                'riddle_text' => 'Sur le papier jauni du grenier, Gali lit : « Cherche l\'endroit où les trains se reposent… » Repère cette grande locomotive immobile figée dans la pierre !',
                'qr_map_code' => 'MAP_LOC_1',
                'qr_mj_code' => 'MJ_STEP_1',
                'code_part' => '4',
            ],
            [
                'id' => 2,
                'name' => 'Étape 2 : Centre-Ville',
                'lat' => 50.4298,
                'lng' => 2.8295,
                'target_radius' => 30,
                'riddle_text' => 'Sur les traces de la vie dans les corons, plonge au cœur de la cité. Utilise la loupe du carnet pour repérer le détail caché sur une façade !',
                'qr_map_code' => 'MAP_LOC_2',
                'qr_mj_code' => 'MJ_STEP_2',
                'code_part' => '8',
            ],
            [
                'id' => 3,
                'name' => 'Étape 3 : Stade Bollaert-Delelis',
                'lat' => 50.4328,
                'lng' => 2.8149,
                'target_radius' => 65,
                'riddle_text' => 'Là où vibre la passion Sang et Or ! Résous l\'énigme autour des couleurs de la ville pour retrouver ce temple légendaire.',
                'qr_map_code' => 'MAP_LOC_3',
                'qr_mj_code' => 'MJ_STEP_3',
                'code_part' => '1',
            ],
            [
                'id' => 4,
                'name' => 'Étape 4 : Musée Louvre-Lens',
                'lat' => 50.4312,
                'lng' => 2.8043,
                'target_radius' => 60,
                'riddle_text' => 'Quand l\'ancienne mine laisse place aux reflets de verre et d\'aluminium… Un grand trésor culturel veille au milieu d\'un vaste parc.',
                'qr_map_code' => 'MAP_LOC_4',
                'qr_mj_code' => 'MJ_STEP_4',
                'code_part' => '9',
            ],
            [
                'id' => 5,
                'name' => 'Étape 5 : Les Terrils de Loos-en-Gohelle',
                'lat' => 50.4485,
                'lng' => 2.7845,
                'target_radius' => 70,
                'riddle_text' => 'Grimpe vers les deux géants noirs jumeaux qui dominent l\'horizon minier pour récupérer le dernier morceau vers le trésor !',
                'qr_map_code' => 'MAP_LOC_5',
                'qr_mj_code' => 'MJ_STEP_5',
                'code_part' => 'Office de Tourisme',
            ],
            [
                'id' => 6,
                'name' => 'Étape 6 : Office de Tourisme',
                'lat' => 50.4289,
                'lng' => 2.8322,
                'target_radius' => 30,
                'riddle_text' => 'Tous tes morceaux enfin réunis mènent au point d\'accueil des voyageurs : c\'est là que t\'attend le vrai coffre de la Lampe Cœur et son code à 4 chiffres !',
                'qr_map_code' => 'MAP_LOC_6',
                'qr_mj_code' => 'MJ_STEP_6',
                'code_part' => 'Coffre Asmodee',
            ],
        ];

        foreach ($steps as $stepData) {
            Step::updateOrCreate(['id' => $stepData['id']], $stepData);
        }
    }
}
