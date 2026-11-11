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
                'riddle_text' => 'Au dos du morceau de clé trouvé dans le grenier, Gali lit sur une vieille note : « Cherche l\'endroit où les trains se reposent… » Repère cette grande locomotive immobile figée dans la pierre !',
                'story_rp' => 'En sortant du grenier avec sa pièce de départ et sa note jaunie, Gali file droit vers les rails ! C\'est ici que son grand-père prenait autrefois le train après ses longues journées. Le premier fragment de la clé antique brille déjà d\'une lueur dorée.',
                'heritage_info' => 'Inaugurée en 1927 et dessinée par l\'architecte Urbain Cassan, la gare de Lens possède une silhouette unique en forme de locomotive à vapeur. À l\'intérieur, les mosaïques Art déco rendent hommage au passé ferroviaire et minier de la ville.',
                'wrong_scan_intro' => 'Ça, c’est la Gare de Lens ! Son grand hall voit passer les voyageurs tous les jours',
                'wrong_scan_mismatch' => 'mais aucun train ne s’y repose !',
                'qr_map_code' => 'MAP_LOC_1',
                'qr_mj_code' => 'MJ_STEP_1',
                'code_part' => 'Bord de l\'anneau',
            ],
            [
                'id' => 2,
                'name' => 'Étape 2 : Centre-Ville',
                'lat' => 50.42903639271235,
                'lng' => 2.8308603520021958,
                'target_radius' => 30,
                'riddle_text' => 'Sur les traces de la vie dans les corons, plonge au cœur de la cité. Utilise la loupe du carnet pour repérer le détail caché sur une façade !',
                'story_rp' => 'Gali range précieusement ce deuxième fragment de la clé et ajuste sa loupe dans les rues animées du cœur de Lens. Chaque façade semble cacher un secret laissé par les anciens mineurs pour guider ses pas.',
                'heritage_info' => 'Reconstruit dans un élégant style Art déco et régionaliste après la Première Guerre mondiale, le centre-ville lensois relie la mémoire des corons aux boulevards commerçants à travers ses briques rouges et ses frontons sculptés.',
                'wrong_scan_intro' => 'Ça, c’est le Centre-Ville de Lens ! Ses façades Art déco racontent la vie d’autrefois',
                'wrong_scan_mismatch' => 'mais aucune façade de coron n’y attend ta loupe !',
                'qr_map_code' => 'MAP_LOC_2',
                'qr_mj_code' => 'MJ_STEP_2',
                'code_part' => 'Base de l\'anneau',
            ],
            [
                'id' => 3,
                'name' => 'Étape 3 : Stade Bollaert-Delelis',
                'lat' => 50.43307979422415,
                'lng' => 2.81708718207351,
                'target_radius' => 65,
                'riddle_text' => 'Là où vibre la passion Sang et Or ! Résous l\'énigme autour des couleurs de la ville pour retrouver ce temple légendaire.',
                'story_rp' => 'Le cœur de Gali bat au rythme des chants lensois ! En approchant des tribunes légendaires, le jeune explorateur décroche un troisième fragment de clé qui semble puiser sa force dans cette ferveur populaire unique.',
                'heritage_info' => 'Construit en 1933 à l\'initiative de Félix Bollaert pour rassembler les familles de mineurs, le stade Bollaert-Delelis incarne l\'âme Sang et Or : le rouge pour le courage des mineurs, l\'or pour la lumière du charbon et de l\'espoir.',
                'wrong_scan_intro' => 'Ça, c’est le stade Bollaert ! Les supporters Sang et Or y chantent Les Corons',
                'wrong_scan_mismatch' => 'mais ce lieu ne vibre pas aux couleurs Sang et Or de l’énigme !',
                'qr_map_code' => 'MAP_LOC_3',
                'qr_mj_code' => 'MJ_STEP_3',
                'code_part' => 'Col sculpté',
            ],
            [
                'id' => 4,
                'name' => 'Étape 4 : Musée Louvre-Lens',
                'lat' => 50.4308,
                'lng' => 2.8044,
                'target_radius' => 60,
                'riddle_text' => 'Quand l\'ancienne mine laisse place aux reflets de verre et d\'aluminium… Un grand trésor culturel veille au milieu d\'un vaste parc.',
                'story_rp' => 'Quelle métamorphose ! Gali traverse l\'ancien carreau de mine devenu un écrin de lumière. Entre les reflets du parc et les œuvres millénaires, un quatrième fragment de la clé antique refait surface.',
                'heritage_info' => 'Installé depuis 2012 sur l\'ancienne fosse 9-9bis des mines de Lens, le Louvre-Lens et son parc paysager symbolisent la renaissance du bassin minier : là où l\'on extrayait le charbon rayonnent aujourd\'hui l\'architecture de verre et l\'art.',
                'wrong_scan_intro' => 'Ça, c’est le Louvre-Lens ! Ses galeries de verre brillent au milieu d’un grand parc',
                'wrong_scan_mismatch' => 'mais on n’y voit ni reflets de verre ni trésor caché dans un parc !',
                'qr_map_code' => 'MAP_LOC_4',
                'qr_mj_code' => 'MJ_STEP_4',
                'code_part' => 'Fût gravé',
            ],
            [
                'id' => 5,
                'name' => 'Étape 5 : Les Terrils de Loos-en-Gohelle',
                'lat' => 50.44497222222222,
                'lng' => 2.786388888888889,
                'target_radius' => 70,
                'riddle_text' => 'Grimpe vers les deux géants noirs jumeaux qui dominent l\'horizon minier pour récupérer le cinquième fragment de la clé !',
                'story_rp' => 'Au sommet de ces montagnes noires nées du travail de ses ancêtres, Gali retient son souffle. Avec ce cinquième fragment en poche, le vent du Pas-de-Calais lui révèle la piste finale vers le coffre secret où sommeille la Lampe Cœur !',
                'heritage_info' => 'Classés au Patrimoine mondial de l\'UNESCO, les terrils jumeaux 11/19 de Loos-en-Gohelle culminent à 186 mètres : ce sont les plus hauts d\'Europe, abritant aujourd\'hui une faune et une flore remarquables avec vue sur tout le bassin.',
                'wrong_scan_intro' => 'Ça, ce sont les Terrils de Loos-en-Gohelle ! Deux géants noirs veillent sur l’horizon',
                'wrong_scan_mismatch' => 'mais on n’y grimpe pas sur deux géants noirs jumeaux à l’horizon !',
                'qr_map_code' => 'MAP_LOC_5',
                'qr_mj_code' => 'MJ_STEP_5',
                'code_part' => 'Garde du fût',
            ],
            [
                'id' => 6,
                'name' => 'Étape 6 : Office de Tourisme',
                'lat' => 50.42934084491061,
                'lng' => 2.832335350234869,
                'target_radius' => 30,
                'riddle_text' => 'La clé est presque complète et ses repères convergent vers le point d\'accueil des voyageurs : décroche le sixième et dernier fragment pour ouvrir le coffre secret et rallumer la Lampe Cœur !',
                'story_rp' => 'Les six fragments s\'emboîtent enfin sur la clé antique de Gali ! Devant l\'Office de Tourisme, la serrure du coffre secret cède dans un éclat doré et la Lampe Cœur brille à nouveau grâce à votre équipe d\'explorateurs.',
                'heritage_info' => 'Situé sur la place Jean-Jaurès au cœur de Lens, l\'Office de Tourisme est la porte d\'entrée de Destination Lens-Liévin et le point de rencontre entre les habitants, les voyageurs et l\'histoire du territoire.',
                'wrong_scan_intro' => 'Ça, c’est l’Office de Tourisme ! Les voyageurs y passent découvrir tous les secrets de Lens',
                'wrong_scan_mismatch' => 'mais ce n’est pas là que les voyageurs ouvrent le coffre de la Lampe Cœur !',
                'qr_map_code' => 'MAP_LOC_6',
                'qr_mj_code' => 'MJ_STEP_6',
                'code_part' => 'Dents de la clé',
            ],
        ];

        foreach ($steps as $stepData) {
            Step::updateOrCreate(['id' => $stepData['id']], $stepData);
        }
    }
}
