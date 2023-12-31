<?php

namespace App;

class BibliothequeManager
{
    private $bibliotheque = [];
    private $file = 'bibli.json';

    // Constructeur de la classe
    public function __construct()
    {
        // $this->genererLivresAleatoires(100);
        $this->loadFile();
    }

    // Méthode pour générer des livres aléatoires
    private function genererLivresAleatoires($nombreLivres)
    {
        $noms = [];
        $descriptions = [];

        for ($i = 1; $i <= $nombreLivres; $i++) {
            $noms[] = "Livre " . $i;
            $descriptions[] = "Description du Livre " . $i . ": Lorem ipsum dolor sit amet, consectetur adipiscing elit.";
        }

        // Ajouter les livres générés à la bibliothèque
        for ($i = 0; $i < $nombreLivres; $i++) {
            $disponibleAleatoire = (bool)rand(0, 1);
            $this->ajouterLivre($noms[$i], $descriptions[$i], $disponibleAleatoire);
        }

        $this->saveFile();
    }

    // Méthode pour ajouter un livre
    public function ajouterLivre($nom, $description, $disponible)
    {
        // Identifiant auto-généré
        $id = uniqid();
        // Coordonner le texte 'oui' ou 'non'
        $disponible = (strtolower($disponible) === 'oui')? 'oui' : 'non';
        // Créer un tableau associatif
        $livre = ['id' => $id, 'nom' => $nom, 'description' => $description, 'disponible' => $disponible];
        $this->bibliotheque[] = $livre;
        echo "Livre ajouté avec succès!\n   - UID = $id\n";
        $this->saveFile();
    }

    // Méthode pour afficher la liste des livres
    public function afficherListeLivres()
    {
        echo "\nListe des Livres:\n";
        foreach ($this->bibliotheque as $livre) {
            echo "    | ID: " . $livre['id'] . "  | Nom: " . $livre['nom'] . "    | Description: " . $livre['description'] . "    | Disponible: " . $livre['disponible'] . "\n";
        }
    }

    // Méthode pour modifier un livre
    public function modifierLivre($id)
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
                        $livre['disponible'] = (strtolower(strtolower(readline("Le livre est-il toujours disponible (Oui/Non): "))) === 'oui')? 'oui' : 'non';
                        break;
                    default:
                        echo "Champ invalide: $champ. Ignoré.\n";
                        break;
                }
            }

            // Mettre à jour le livre dans la bibliothèque
            $this->bibliotheque[$index] = $livre;

            echo "Livre modifié avec succès!\n";

            $this->saveFile();
        } else {
            echo "Livre non trouvé.\n";
        }
    }

    // Méthode pour supprimer un livre
    public function supprimerLivre($param)
    {
        $livresSupprimes = 0;

        foreach ($this->bibliotheque as $index => $livre) {
            if (
                $livre['id'] == $param ||
                strtolower($livre['nom']) == strtolower($param) ||
                strtolower($livre['description']) == strtolower($param)
            ) {
                unset($this->bibliotheque[$index]);
                $livresSupprimes++;
            }
        }

        if ($livresSupprimes > 0) {
            echo "Livre(s) supprimé(s) avec succès!\n";
        } else {
            echo "Aucun livre trouvé pour la suppression.\n";
        }

        // Réorganiser les indices du tableau
        $this->bibliotheque = array_values($this->bibliotheque);

        $this->saveFile();
    }

    // Méthode pour afficher les données d'un seul livre
    public function afficherLivre($id)
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

    // Méthode pour trier les livres
    public function trierLivres($colonne, $ordre = 'asc')
    {
        // Vérifier si la colonne est valide
        $colonnesValides = ['nom', 'description', 'disponible'];
        if (!in_array($colonne, $colonnesValides)) {
            echo "La colonne de tri spécifiée n'est pas valide.\n";
            return;
        }

        // Appeler la fonction de tri fusion
        $this->fusionSort($colonne, $ordre);

        echo "Livres triés par $colonne dans l'ordre $ordre avec succès!\n";
    }

    // Fonction de tri fusion récursive
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

        $array = $this->fusionner($colonne, $ordre, $left, $right);
    }

    // Fonction de fusion pour le tri fusion
    private function fusionner($colonne, $ordre, $left, $right)
    {
        $result = [];
        $leftIndex = 0;
        $rightIndex = 0;

        while ($leftIndex < count($left) && $rightIndex < count($right)) {
            if ($this->compareLivres($colonne, $ordre, $left[$leftIndex], $right[$rightIndex])) {
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

    // Fonction pour comparer deux livres en fonction de la colonne spécifiée
    private function compareLivres($colonne, $ordre, $livre1, $livre2)
    {
        $valeur1 = $livre1[$colonne];
        $valeur2 = $livre2[$colonne];

        if ($colonne === 'nom') {
            return $ordre === 'asc' ? strcasecmp($valeur1, $valeur2) <= 0 : strcasecmp($valeur1, $valeur2) >= 0;
        } else {
            return $ordre === 'asc' ? $valeur1 <= $valeur2 : $valeur1 >= $valeur2;
        }
    }

    // Méthode pour rechercher un livre dans une colonne spécifique
    public function rechercherLivre($colonne, $valeur)
    {
        // Assurer que la bibliothèque est triée avant la recherche
        $this->trierLivres($colonne, 'asc');

        // Appeler la fonction de recherche binaire
        $index = $this->rechercheBinaire($colonne, $valeur);

        if ($index !== false) {
            $livre = $this->bibliotheque[$index];
            echo "Livre trouvé:\n";
            echo "Nom: " . $livre['nom'] . "\n";
            echo "Description: " . $livre['description'] . "\n";
            echo "Identifiant: " . $livre['id'] . "\n";
            echo "Disponible en stock: " . ($livre['disponible'] ? 'Oui' : 'Non') . "\n";
        } else {
            echo "Livre non trouvé.\n";
        }
    }

    // Fonction de recherche binaire récursive
    private function rechercheBinaire($colonne, $valeur, $gauche = 0, $droite = null)
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
                return $this->rechercheBinaire($colonne, $valeur, $milieu + 1, $droite);
            } else {
                return $this->rechercheBinaire($colonne, $valeur, $gauche, $milieu - 1);
            }
        }

        return false;
    }

    // BONUS

    // Méthode pour sauvegarder la bibliothèque dans un fichier JSON
    public function saveFile($file = null)
    {
        if ($file === null) {
            $file = $this->file;
        }

        $json = json_encode($this->bibliotheque, JSON_PRETTY_PRINT);
        file_put_contents($file, $json);
        echo "La bibliothèque a été sauvegardée dans le fichier $file.\n";
    }

    // Méthode pour charger la bibliothèque depuis un fichier JSON
    public function loadFile($file = null)
    {
        if ($file === null) {
            $file = $this->file;
        }
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $this->bibliotheque = json_decode($json, true);
            echo "La bibliothèque a été chargée depuis le fichier $file.\n";
        } else {
            echo "Le fichier $file n'existe pas. Une nouvelle bibliothèque vide a été créée.\n";
        }
    }

    


    // Méthode pour afficher le menu
    public function afficherMenu()
    {
        echo "Menu:\n";
        echo "1. Ajouter un Livre\n";
        echo "2. Afficher la Liste des Livres\n";
        echo "3. Modifier un Livre\n";
        echo "4. Supprimer un Livre\n";
        echo "5. Afficher un Livre\n";
        echo "6. Trier les Livres\n";
        echo "7. Rechercher un Livre\n";
        echo "8. Sauvegarder la bibliothèque dans un fichier JSON\n";
        echo "9. Générer des livres aléatoires\n";
        echo "10. Quitter\n";
    }

    // Méthode pour exécuter le programme
    public function executer()
    {
        do {
            $this->afficherMenu();
            echo "Choisissez une option (1-10): ";
            $choix = trim(fgets(STDIN));

            switch ($choix) {
                case 1:
                    $this->ajouterLivre(
                        readline("Entrez le nom du livre: "),
                        readline("Entrez la description du livre: "),
                        readline("Le livre est-il disponible (Oui/Non): ")
                    );
                    break;
                case 2:
                    $this->afficherListeLivres();
                    break;
                case 3:
                    $this->modifierLivre(
                        readline("Entrez l'identifiant du livre à modifier: ")
                    );
                    break;
                case 4:
                    // Supprimer un Livre
                    $param = readline("Entrez l'identifiant, le nom, la description ou la disponibilité du livre à supprimer: ");
                    $this->supprimerLivre($param);
                    break;
                case 5:
                    // Afficher un Livre
                    $this->afficherLivre(
                        readline("Entrez l'identifiant du livre à afficher: ")
                    );
                    break;
                case 6:
                    // Trier les Livres
                    $colonne = readline("Entrez la colonne de tri (nom, description ou disponible): ");
                    $ordre = readline("Entrez l'ordre de tri (asc ou desc): ");
                    $this->trierLivres($colonne, $ordre);
                    break;
                case 7:
                    // Rechercher un Livre
                    $colonne = readline("Entrez la colonne de recherche (nom, description ou disponible): ");
                    $valeur = readline("Entrez la valeur à rechercher: ");
                    $this->rechercherLivre($colonne, $valeur);
                    break;
                case 8:
                    // Sauvegarder la bibliothèque dans un fichier JSON
                    $this->saveFile();
                    break;
                case 9:
                    // Générer des livres aléatoires
                    $nombreLivres = readline("Entrez le nombre de livres à générer: ");
                    $this->genererLivresAleatoires($nombreLivres);
                    break;
                case 10:
                    // quitter
                    // echo "  - Au revoir!\n";
                    exit();
                    break;
                default:
                    echo "  - Choix invalide!\n";
                    exit();
            }
        } while ($choix != 5);
    }

    // Méthode auxiliaire pour trouver l'index d'un livre par son ID
    private function trouverIndexLivre($id)
    {
        foreach ($this->bibliotheque as $index => $livre) {
            if ($livre['id'] == $id) {
                return $index;
            }
        }
        return false; // Livre non trouvé
    }
}

// Exemple d'utilisation de la classe BibliothequeManager
$manager = new BibliothequeManager();
$manager->executer();
