<?php

namespace App;

class BibliothequeManager
{
    private $bibliotheque = [];
    private $historique = [];
    private $bibliFile = 'bibli.json';
    private $historyFile = 'history.txt';

    public function __construct()
    {
        // $this->generateBooks(100);
        $this->loadFile($this->bibliFile, "bibliotheque");
        $this->loadFile($this->historyFile, "historique");
    }

    // faire la sauvegarde lors de la destruction de l'objet
    // ne save pas lors de crash ou ctrl + c
    // public function __destruct()
    // {
    //     $this->saveFile($this->bibliFile, "bibliotheque");
    //     $this->saveFile($this->historyFile, "historique");
    // }

    /*
     * @description: Générer des livres
     * @param $nombreLivres: nombre de livres à générer
     * 
     * @return void
    */
    private function generateBooks($nombreLivres)
    {
        $noms = [];
        $descriptions = [];

        for ($i = 1; $i <= $nombreLivres; $i++) {
            $noms[] = "Livre " . $i;
            $descriptions[] = "Description du Livre " . $i;
        }

        for ($i = 0; $i < $nombreLivres; $i++) {
            $disponibleAleatoire = (bool)rand(0, 1);
            $this->addBook($noms[$i], $descriptions[$i], $disponibleAleatoire);
        }
    }

    /*
     * @description: Ajouter un livre
     * @param $nom: nom du livre
     * @param $description: description du livre
     * @param $disponible: disponibilité du livre
     * 
     * @return void
    */
    public function addBook($nom, $description, $disponible)
    {
        $id = uniqid();     // Identifiant auto-généré
        $disponible = (strtolower($disponible) === 'oui')? 'oui' : 'non';   // Coordonner le texte 'oui' ou 'non'

        $livre = ['id' => $id, 'nom' => $nom, 'description' => $description, 'disponible' => $disponible];
        $this->bibliotheque[] = $livre;
        echo "Livre ajouté avec succès!\n   - UID = $id\n";

        $this->saveFile($this->bibliFile, "bibliotheque");

        $this->addHistory("Ajout d'un livre [ ID: $id, Nom: $nom, Description: $description, Disponible: " . $disponible . " ]");
    }

    /*
     * @description: Afficher la liste des livres
     * 
     * @return void
    */
    public function showBookslist()
    {
        echo "\nListe des Livres:\n";
        if (count($this->bibliotheque) === 0) {
            echo "  - Aucun livre trouvé.\n";
            return;
        }
        foreach ($this->bibliotheque as $livre) {
            echo "    | ID: " . $livre['id'] . "  | Nom: " . $livre['nom'] . "    | Description: " . $livre['description'] . "    | Disponible: " . $livre['disponible'] . "\n";
        }
    }

    /*
     * @description: Modifier un livre
     * @param $id: identifiant du livre à modifier
     *
     * @return void
    */
    public function modifyBook($id)
    {
        $index = $this->trouverIndexLivre($id);
        if ($index !== false) {
            $livre = $this->bibliotheque[$index];
            
            echo "\n\nInformations actuelles du livre:\n";
            print_r($livre);

            echo "\n\nChoisissez les champs à modifier (séparés par des virgules, par exemple: nom,description,disponible): ";
            $champs = trim(fgets(STDIN));
            $champsAModifier = explode(',', $champs);

            foreach ($champsAModifier as $champ) {
                switch (trim($champ)) {
                    case 'nom':
                        $livre['nom'] = readline("Entrez le nouveau nom: ");
                        break;
                    case 'description':
                        $livre['description'] = readline("Entrez la nouvelle description: ");
                        break;
                    case 'disponible':
                        $livre['disponible'] = (strtolower(trim(readline("Le livre est-il toujours disponible (Oui/Non): "))) === 'oui')? 'oui' : 'non';
                        break;
                    default:
                        echo "Champ invalide: $champ. Ignoré.\n";
                        break;
                }
            }

            $data = $this->bibliotheque[$index];

            $this->bibliotheque[$index] = $livre;

            echo "Livre modifié avec succès!\n";

            $this->saveFile($this->bibliFile, "bibliotheque");

            $this->addHistory("Modification d'un livre\n".
                "       [ ID: ". $data['id'] .", Nom: " . $data['nom'] . ", Description: " . $data['description'] . ", Disponible: " . $data['disponible'] . " ]\n".
                "       [ ID: $id, Nom: " . $livre['nom'] . ", Description: " . $livre['description'] . ", Disponible: " . $livre['disponible'] . " ]\n"
            );
        } else {
            echo "Livre non trouvé.\n";
        }
    }

    /*
     * @description: Supprimer un livre
     * @param $param: identifiant, nom, description du livre à supprimer
     *
     * @return void
    */
    public function deleteBook($param)
    {
        $livresSupprimes = 0;
        $data = "";

        foreach ($this->bibliotheque as $index => $livre) {
            if (
                $livre['id'] == $param ||
                strtolower($livre['nom']) == strtolower($param) ||
                strtolower($livre['description']) == strtolower($param)
            ) {
                $data .= "       [ ID: ". $this->bibliotheque[$index]['id'] .", Nom: " . $this->bibliotheque[$index]['nom'] . ", Description: " . $this->bibliotheque[$index]['description'] . ", Disponible: " . $this->bibliotheque[$index]['disponible'] . " ]\n";

                unset($this->bibliotheque[$index]);
                $livresSupprimes++;
            }
        }

        if ($livresSupprimes > 0) {
            echo "Livre(s) supprimé(s) avec succès!\n";
        } else {
            echo "Aucun livre trouvé pour la suppression.\n";
        }

        $this->bibliotheque = array_values($this->bibliotheque);

        $this->saveFile($this->bibliFile, "bibliotheque");

        $this->addHistory("Suppression d'un livre [ Paramètre: $param, Livres supprimés: $livresSupprimes ]\n". $data);
    }

    /*
     * @description: Afficher les données d'un seul livre
     * @param $id: identifiant du livre à afficher
     *
     * @return void
    */
    public function showBook($id)
    {
        $index = $this->trouverIndexLivre($id);
        if ($index !== false) {
            $livre = $this->bibliotheque[$index];
            
            echo "Nom: " . $livre['nom'] . "\n";
            echo "Description: " . $livre['description'] . "\n";
            echo "Identifiant: " . $livre['id'] . "\n";
            echo "Disponible en stock: " . $livre['disponible'] . "\n";
        } else {
            echo "Livre non trouvé.\n";
        }
    }

    /*
     * @description: Trier les livres
     * @param $colonne: colonne de tri
     * @param $ordre: ordre de tri
     *
     * @return void
    */
    public function sortBooks($colonne, $ordre = 'asc')
    {
        $colonnesValides = ['nom', 'description', 'disponible'];
        if (!in_array($colonne, $colonnesValides)) {
            echo "La colonne de tri spécifiée n'est pas valide.\n";
            return;
        }

        $this->fusionSort($colonne, $ordre);

        echo "Livres triés par $colonne dans l'ordre $ordre avec succès!\n";
    }

    /*
     * @description: Trouver l'index d'un livre par son ID
     * @param $id: identifiant du livre à trouver
     *
     * @return int|bool
    */
    private function trouverIndexLivre($id)
    {
        foreach ($this->bibliotheque as $index => $livre) {
            if ($livre['id'] == $id) {
                return $index;
            }
        }
        return false;
    }

    /*
     * @description: Tri fusion récursif
     * @param $colonne: colonne de tri
     * @param $ordre: ordre de tri
     * @param $array: tableau à trier
     *
     * @return void
    */
    private function fusionSort($colonne, $ordre, &$array = null)
    {
        if ($array === null) {
            $array = &$this->bibliotheque;
        }

        $length = count($array);
        if ($length <= 1) {
            return;
        }

        $middle = floor($length / 2);

        $left = array_slice($array, 0, $middle);
        $right = array_slice($array, $middle);

        $this->fusionSort($colonne, $ordre, $left);
        $this->fusionSort($colonne, $ordre, $right);

        $array = $this->fusion($colonne, $ordre, $left, $right);
    }

    /*
     * @description: Fonction de fusion pour le tri fusion
     * @param $colonne: colonne de tri
     * @param $ordre: ordre de tri
     * @param $left: tableau gauche
     * @param $right: tableau droit
     *
     * @return array
    */
    private function fusion($colonne, $ordre, $left, $right)
    {
        $result = [];
        $leftIndex = 0;
        $rightIndex = 0;

        while ($leftIndex < count($left) && $rightIndex < count($right)) {
            if ($this->compareBooks($colonne, $ordre, $left[$leftIndex], $right[$rightIndex])) {
                $result[] = $left[$leftIndex++];
            } else {
                $result[] = $right[$rightIndex++];
            }
        }

        while ($leftIndex < count($left)) {
            $result[] = $left[$leftIndex++];
        }

        while ($rightIndex < count($right)) {
            $result[] = $right[$rightIndex++];
        }

        return $result;
    }

    /*
     * @description: Comparer deux livres en fonction de la colonne spécifiée
     * @param $colonne: colonne de tri
     * @param $ordre: ordre de tri
     * @param $livre1: livre 1
     * @param $livre2: livre 2
     *
     * @return bool
    */
    private function compareBooks($colonne, $ordre, $livre1, $livre2)
    {
        $valeur1 = $livre1[$colonne];
        $valeur2 = $livre2[$colonne];

        if ($colonne === 'nom') {
            return $ordre === 'asc' ? strcasecmp($valeur1, $valeur2) <= 0 : strcasecmp($valeur1, $valeur2) >= 0;
        } else {
            return $ordre === 'asc' ? $valeur1 <= $valeur2 : $valeur1 >= $valeur2;
        }
    }

    /*
     * @description: Rechercher un livre dans une colonne spécifique
     * @param $colonne: colonne de recherche
     * @param $valeur: valeur à rechercher
     *
     * @return void
    */
    public function searchBook($colonne, $valeur)
    {
        $this->sortBooks($colonne, 'asc');

        $index = $this->searchBinary($colonne, $valeur);

        if ($index !== false) {
            $livre = $this->bibliotheque[$index];
            echo "Livre trouvé:\n";
            echo "Nom: " . $livre['nom'] . "\n";
            echo "Description: " . $livre['description'] . "\n";
            echo "Identifiant: " . $livre['id'] . "\n";
            echo "Disponible en stock: " . ($livre['disponible'] ? 'Oui' : 'Non') . "\n";

            $this->addHistory("Recherche d'un livre [ Colonne: $colonne, Valeur: $valeur, Livre trouvé: Oui ]\n". 
                "   [ ID: ". $this->bibliotheque[$index]['id'] .", Nom: " . $this->bibliotheque[$index]['nom'] . ", Description: " . $this->bibliotheque[$index]['description'] . ", Disponible: " . $this->bibliotheque[$index]['disponible'] . " ]\n"
            );
        } else {
            echo "Livre non trouvé.\n";

            $this->addHistory("Recherche d'un livre [ Colonne: $colonne, Valeur: $valeur, Livre trouvé: Non ]");
        }
    }

    /*
     * @description: Recherche binaire récursive
     * @param $colonne: colonne de recherche
     * @param $valeur: valeur à rechercher
     * @param $gauche: index de gauche
     * @param $droite: index de droite
     *
     * @return int|bool
    */
    private function searchBinary($colonne, $valeur, $gauche = 0, $droite = null)
    {
        if ($droite === null) {
            $droite = count($this->bibliotheque) - 1;
        }

        if ($gauche <= $droite) {
            $milieu = $gauche + floor(($droite - $gauche) / 2);

            $comparaison = strcasecmp($this->bibliotheque[$milieu][$colonne], $valeur);

            if ($comparaison === 0) {
                return $milieu;
            }

            if ($comparaison < 0) {
                return $this->searchBinary($colonne, $valeur, $milieu + 1, $droite);
            } else {
                return $this->searchBinary($colonne, $valeur, $gauche, $milieu - 1);
            }
        }

        return false;
    }

    // BONUS

    /*
     * @description: Sauvegarder un tableau dans un fichier JSON
     * @param $file: fichier de sauvegarde
     * @param $data: données à sauvegarder
     *
     * @return void
    */
    public function saveFile($file = null, $data = null)
    {
        $json = json_encode($this->$data, JSON_PRETTY_PRINT);
        file_put_contents($file, $json);
        echo "La bibliothèque a été sauvegardée dans le fichier $file.\n";
    }

    /*
     * @description: Charger un tableau depuis un fichier JSON
     * @param $file: fichier de chargement
     * @param $data: données à charger
     *
     * @return void
    */
    public function loadFile($file = null, $data = null)
    {
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->$data = json_decode($json, true);

            echo "Le fichier $file a été chargée.\n";
        } else {
            echo "Le fichier $file n'existe pas.\n";
        }
    }

    /*
     * @description: Ajouter une opération à l'historique
     * @param $msg: message à ajouter
     *
     * @return void
    */
    private function addHistory($msg)
    {
        $timestamp = date('Y-m-d H:i:s');

        $this->historique[] = ['timestamp' => $timestamp, 'message' => $msg];

        $this->saveFile($this->historyFile, "historique");
    }

    /*
     * @description: Afficher l'historique
     *
     * @return void
    */
    public function showHistory()
    {
        echo "Historique des actions:\n";
        foreach ($this->historique as $action) {
            echo "  - " . $action['timestamp'] . " : " . $action['message'] . "\n";
        }
    }

    /*
     * @description: Afficher le menu
     *
     * @return void
    */
    public function displayMenu()
    {
        echo "Menu:\n";
        echo "1. Ajouter un Livre\n";
        echo "2. Afficher la Liste des Livres\n";
        echo "3. Modifier un Livre\n";
        echo "4. Supprimer un Livre\n";
        echo "5. Afficher un Livre\n";
        echo "6. Trier les Livres\n";
        echo "7. Rechercher un Livre\n";
        echo "8. Générer des livres aléatoires\n";
        echo "9. Afficher l'historique\n";
        echo "\nQ. Quitter\n";
    }

    /*
     * @description: Exécuter le programme
     *
     * @return void
    */
    public function menu()
    {
        do {
            $this->displayMenu();
            echo "Choisissez une option (1-10): ";
            $choix = trim(fgets(STDIN));

            switch ($choix) {
                case 1:
                    $this->addBook(
                        readline("Entrez le nom du livre: "),
                        readline("Entrez la description du livre: "),
                        readline("Le livre est-il disponible (Oui/Non): ")
                    );
                    break;
                case 2:
                    $this->showBookslist();
                    break;
                case 3:
                    $this->modifyBook(
                        readline("Entrez l'identifiant du livre à modifier: ")
                    );
                    break;
                case 4:
                    // Supprimer un Livre
                    $param = readline("Entrez l'identifiant, le nom, la description ou la disponibilité du livre à supprimer: ");
                    $this->deleteBook($param);
                    break;
                case 5:
                    // Afficher un Livre
                    $this->showBook(
                        readline("Entrez l'identifiant du livre à afficher: ")
                    );
                    break;
                case 6:
                    // Trier les Livres
                    $colonne = readline("Entrez la colonne de tri (nom, description ou disponible): ");
                    $ordre = readline("Entrez l'ordre de tri (asc ou desc): ");
                    $this->sortBooks($colonne, $ordre);
                    break;
                case 7:
                    // Rechercher un Livre
                    $colonne = readline("Entrez la colonne de recherche (nom, description ou disponible): ");
                    $valeur = readline("Entrez la valeur à rechercher: ");
                    $this->searchBook($colonne, $valeur);
                    break;
                case 8:
                    // Générer des livres aléatoires
                    $nombreLivres = readline("Entrez le nombre de livres à générer: ");
                    $this->generateBooks($nombreLivres);
                    break;
                case 9:
                    // Afficher l'historique
                    $this->showHistory();
                    break;
                case 'q':
                case 'Q':
                    exit();
                    break;
                default:
                    echo "  - Choix invalide!\n";
            }
        } while ($choix != 10);
    }
}

// Exemple d'utilisation de la classe BibliothequeManager
$bibli = new BibliothequeManager();

// $bibli->addBook('Livre add by class', 'Description du Livre 1', 'oui');

$bibli->menu();
