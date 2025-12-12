<?php
require_once 'includes/models/Trajet.php';
require_once 'includes/utils/Validator.php';

class TrajetController {
    private $db;
    private $trajetModel;
    private $validator;

    public function __construct($db) {
        $this->db = $db;
        $this->trajetModel = new Trajet($db);
        $this->validator = new Validator();
    }

    // Publier un trajet
    public function publierTrajet($conducteur_id, $lieu_depart, $lieu_arrivee, $date_trajet, $heure_depart, $places_disponibles, $prix, $description = '', $vehicule = '') {
        $response = array('success' => false, 'message' => '', 'trajet_id' => null);

        // Validation des données
        if(empty($lieu_depart) || empty($lieu_arrivee) || empty($date_trajet) || empty($heure_depart)) {
            $response['message'] = 'Tous les champs obligatoires doivent être remplis';
            return $response;
        }

        if(!$this->validator->validatePlaces($places_disponibles)) {
            $response['message'] = 'Nombre de places invalide (1-8)';
            return $response;
        }

        if(!empty($prix) && !$this->validator->validatePrice($prix)) {
            $response['message'] = 'Prix invalide';
            return $response;
        }

        // Vérifier que la date est future
        if($this->validator->validateDate($date_trajet)) {
            $trajetDate = new DateTime($date_trajet);
            $today = new DateTime();
            if($trajetDate < $today) {
                $response['message'] = 'La date du trajet doit être future';
                return $response;
            }
        } else {
            $response['message'] = 'Date invalide';
            return $response;
        }

        // Créer le trajet
        $this->trajetModel->conducteur_id = $conducteur_id;
        $this->trajetModel->lieu_depart = $lieu_depart;
        $this->trajetModel->lieu_arrivee = $lieu_arrivee;
        $this->trajetModel->date_trajet = $date_trajet;
        $this->trajetModel->heure_depart = $heure_depart;
        $this->trajetModel->places_disponibles = $places_disponibles;
        $this->trajetModel->prix = $prix ?: 0;
        $this->trajetModel->description = $description;
        $this->trajetModel->vehicule = $vehicule;
        $this->trajetModel->status = 'active';

        $trajet_id = $this->trajetModel->create();

        if($trajet_id) {
            $response['success'] = true;
            $response['message'] = 'Trajet publié avec succès !';
            $response['trajet_id'] = $trajet_id;
        } else {
            $response['message'] = 'Erreur lors de la publication du trajet';
        }

        return $response;
    }

    // Rechercher des trajets
    public function rechercherTrajets($lieu_depart = '', $lieu_arrivee = '', $date = '', $min_places = 1) {
        return $this->trajetModel->getTrajetsWithFilters($lieu_depart, $lieu_arrivee, $date, $min_places);
    }

    // Récupérer les trajets d'un utilisateur
    public function getTrajetsUtilisateur($user_id) {
        return $this->trajetModel->getTrajetsByConducteur($user_id);
    }

    // Supprimer un trajet
    public function supprimerTrajet($trajet_id, $user_id) {
        $response = array('success' => false, 'message' => '');

        $trajet = $this->trajetModel->getTrajetById($trajet_id);
        
        if(!$trajet) {
            $response['message'] = 'Trajet introuvable';
            return $response;
        }

        // Vérifier que l'utilisateur est le conducteur
        if($trajet['conducteur_id'] != $user_id) {
            $response['message'] = 'Vous n\'êtes pas autorisé à supprimer ce trajet';
            return $response;
        }

        if($this->trajetModel->delete($trajet_id)) {
            $response['success'] = true;
            $response['message'] = 'Trajet supprimé avec succès';
        } else {
            $response['message'] = 'Erreur lors de la suppression';
        }

        return $response;
    }

    // Récupérer les trajets récents
    public function getTrajetsRecents($limit = 6) {
        return $this->trajetModel->getLatestTrajets($limit);
    }
}
?>