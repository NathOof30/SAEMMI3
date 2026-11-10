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
                'name' => 'Étape 1 : Place Jean Jaurès',
                'lat' => 50.4293,
                'lng' => 2.8315,
                'riddle_text' => 'Cherchez l\'homme de bronze qui veille sur la grande place.',
                'qr_map_code' => 'MAP_LOC_1',
                'qr_mj_code' => 'MJ_STEP_1',
                'code_part' => '4',
            ],
            [
                'id' => 2,
                'name' => 'Étape 2 : Gare de Lens',
                'lat' => 50.4277,
                'lng' => 2.8258,
                'riddle_text' => 'Je suis une locomotive immobile, figée dans la pierre et l\'architecture.',
                'qr_map_code' => 'MAP_LOC_2',
                'qr_mj_code' => 'MJ_STEP_2',
                'code_part' => '8',
            ],
            [
                'id' => 3,
                'name' => 'Étape 3 : Musée Louvre-Lens',
                'lat' => 50.4312,
                'lng' => 2.8043,
                'riddle_text' => 'Aux reflets de verre et d\'aluminium se cachent les trésors de l\'histoire.',
                'qr_map_code' => 'MAP_LOC_3',
                'qr_mj_code' => 'MJ_STEP_3',
                'code_part' => '1',
            ],
            [
                'id' => 4,
                'name' => 'Étape 4 : Stade Bollaert-Delelis',
                'lat' => 50.4328,
                'lng' => 2.8149,
                'riddle_text' => 'Là où résonnent les chants Sang et Or au cœur du bassin minier.',
                'qr_map_code' => 'MAP_LOC_4',
                'qr_mj_code' => 'MJ_STEP_4',
                'code_part' => '9',
            ],
            [
                'id' => 5,
                'name' => 'Étape 5 : Les Terrils Jumeaux',
                'lat' => 50.4485,
                'lng' => 2.7845,
                'riddle_text' => 'Au pied des géants noirs qui dominent le paysage d\'autrefois.',
                'qr_map_code' => 'MAP_LOC_5',
                'qr_mj_code' => 'MJ_STEP_5',
                'code_part' => 'Office de Tourisme',
            ],
            [
                'id' => 6,
                'name' => 'Étape 6 : Église Saint-Léger',
                'lat' => 50.4305,
                'lng' => 2.8336,
                'riddle_text' => 'Devant le grand portail gravé se trouve la clé du mystère final.',
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
