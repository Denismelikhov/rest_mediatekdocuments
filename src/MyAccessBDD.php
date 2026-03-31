<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "commandedocument" :
                return $this->selectCommandesDocument($champs);
            case "commanderevue" :
                return $this->selectCommandesRevue($champs);
            case "commanderevueexpiration" :
                return $this->selectCommandesRevueAExpirationProche();
            case "service" :
                return $this->selectTableSimple($table);
            case "utilisateur" :
                return $this->selectUtilisateurs();
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
            case "suivi" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * récupère tous les utilisateurs avec le libellé du service
     * @return array|null
     */
    private function selectUtilisateurs() : ?array{
        $requete = "select u.id, u.login, u.idService, s.libelle as service ";
        $requete .= "from utilisateur u ";
        $requete .= "join service s on s.id = u.idService ";
        $requete .= "order by u.login ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->insertLivre($champs);
            case "dvd" :
                return $this->insertDvd($champs);
            case "revue" :
                return $this->insertRevue($champs);
            case "commandedocument" :
                return $this->insertCommandeDocument($champs);
            case "commanderevue" :
                return $this->insertCommandeRevue($champs);
            default:
                return $this->insertOneTupleOneTable($table, $champs);  
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->updateLivre($id, $champs);
            case "dvd" :
                return $this->updateDvd($id, $champs);
            case "revue" :
                return $this->updateRevue($id, $champs);
            case "commandedocument" :
                return $this->updateCommandeDocument($id, $champs);
            case "commanderevue" :
                return $this->updateCommandeRevue($id, $champs);
            case "exemplaire" :
                return $this->updateExemplaire($id, $champs);
            default:                    
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }   
    }
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->deleteLivre($champs);
            case "dvd" :
                return $this->deleteDvd($champs);
            case "revue" :
                return $this->deleteRevue($champs);
            case "commandedocument" :
                return $this->deleteCommandeDocument($champs);
            case "commanderevue" :
                return $this->deleteCommandeRevue($champs);
            case "exemplaire" :
                return $this->deleteExemplaire($champs);
            default:
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);
        }
    }    
            
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }

    /**
     * Retourne les commandes d'un document de type livre ou dvd
     * @param array|null $champs champs contenant au minimum l'id du document
     * @return array|null
     */
    private function selectCommandesDocument(?array $champs) : ?array{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array(
            'id' => $champs['id']
        );

        $requete = "select c.id, c.dateCommande, c.montant, c.idSuivi, s.libelle as libelleSuivi, ";
        $requete .= "cd.nbExemplaire, cd.idLivreDvd ";
        $requete .= "from commandedocument cd ";
        $requete .= "join commande c on c.id = cd.id ";
        $requete .= "join suivi s on s.id = c.idSuivi ";
        $requete .= "where cd.idLivreDvd = :id ";
        $requete .= "order by c.dateCommande desc";

        return $this->conn->queryBDD($requete, $parametres);
    }

    /**
     * supprime un livre à partir de son id
     * suppression dans livre puis livres_dvd puis document
     * @param array|null $champs
     * @return int|null
     */
    private function deleteLivre(?array $champs) : ?int{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array(
            'id' => $champs['id']
        );

        $nb1 = $this->conn->updateBDD("delete from livre where id = :id", $parametres);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->conn->updateBDD("delete from livres_dvd where id = :id", $parametres);
        if ($nb2 === null) {
            return null;
        }

        $nb3 = $this->conn->updateBDD("delete from document where id = :id", $parametres);
        if ($nb3 === null) {
            return null;
        }

        return $nb1 + $nb2 + $nb3;
    }

    /**
     * supprime un dvd à partir de son id
     * suppression dans dvd puis livres_dvd puis document
     * @param array|null $champs
     * @return int|null
     */
    private function deleteDvd(?array $champs) : ?int{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array(
            'id' => $champs['id']
        );

        $nb1 = $this->conn->updateBDD("delete from dvd where id = :id", $parametres);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->conn->updateBDD("delete from livres_dvd where id = :id", $parametres);
        if ($nb2 === null) {
            return null;
        }

        $nb3 = $this->conn->updateBDD("delete from document where id = :id", $parametres);
        if ($nb3 === null) {
            return null;
        }

        return $nb1 + $nb2 + $nb3;
    }

    /**
     * supprime une revue à partir de son id
     * suppression dans revue puis document
     * @param array|null $champs
     * @return int|null
     */
    private function deleteRevue(?array $champs) : ?int{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array(
            'id' => $champs['id']
        );

        $nb1 = $this->conn->updateBDD("delete from revue where id = :id", $parametres);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->conn->updateBDD("delete from document where id = :id", $parametres);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }	

    private function insertLivre(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }

        $doc = array(
            "id" => $champs["id"],
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $livresDvd = array(
            "id" => $champs["id"]
        );

        $livre = array(
            "id" => $champs["id"],
            "ISBN" => $champs["isbn"],
            "auteur" => $champs["auteur"],
            "collection" => $champs["collection"]
        );

        $nb1 = $this->insertOneTupleOneTable("document", $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->insertOneTupleOneTable("livres_dvd", $livresDvd);
        if ($nb2 === null) {
            return null;
        }

        $nb3 = $this->insertOneTupleOneTable("livre", $livre);
        if ($nb3 === null) {
            return null;
        }

        return $nb1 + $nb2 + $nb3;
    }

    private function updateLivre(?string $id, ?array $champs) : ?int{
        if (empty($champs) || is_null($id)) {
            return null;
        }

        $doc = array(
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $livre = array(
            "ISBN" => $champs["isbn"],
            "auteur" => $champs["auteur"],
            "collection" => $champs["collection"]
        );

        $nb1 = $this->updateOneTupleOneTable("document", $id, $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->updateOneTupleOneTable("livre", $id, $livre);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function insertDvd(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }

        $doc = array(
            "id" => $champs["id"],
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $livresDvd = array(
            "id" => $champs["id"]
        );

        $dvd = array(
            "id" => $champs["id"],
            "duree" => $champs["duree"],
            "realisateur" => $champs["realisateur"],
            "synopsis" => $champs["synopsis"]
        );

        $nb1 = $this->insertOneTupleOneTable("document", $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->insertOneTupleOneTable("livres_dvd", $livresDvd);
        if ($nb2 === null) {
            return null;
        }

        $nb3 = $this->insertOneTupleOneTable("dvd", $dvd);
        if ($nb3 === null) {
            return null;
        }

        return $nb1 + $nb2 + $nb3;
    }	    

    private function updateDvd(?string $id, ?array $champs) : ?int{
        if (empty($champs) || is_null($id)) {
            return null;
        }

        $doc = array(
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $dvd = array(
            "duree" => $champs["duree"],
            "realisateur" => $champs["realisateur"],
            "synopsis" => $champs["synopsis"]
        );

        $nb1 = $this->updateOneTupleOneTable("document", $id, $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->updateOneTupleOneTable("dvd", $id, $dvd);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    } 

    private function insertRevue(?array $champs) : ?int{
        if (empty($champs)) {
            return null;
        }

        $doc = array(
            "id" => $champs["id"],
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $revue = array(
            "id" => $champs["id"],
            "periodicite" => $champs["periodicite"],
            "delaiMiseADispo" => $champs["delaiMiseADispo"]
        );

        $nb1 = $this->insertOneTupleOneTable("document", $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->insertOneTupleOneTable("revue", $revue);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function updateRevue(?string $id, ?array $champs) : ?int{
        if (empty($champs) || is_null($id)) {
            return null;
        }

        $doc = array(
            "titre" => $champs["titre"],
            "image" => $champs["image"],
            "idGenre" => $champs["idGenre"],
            "idPublic" => $champs["idPublic"],
            "idRayon" => $champs["idRayon"]
        );

        $revue = array(
            "periodicite" => $champs["periodicite"],
            "delaiMiseADispo" => $champs["delaiMiseADispo"]
        );

        $nb1 = $this->updateOneTupleOneTable("document", $id, $doc);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->updateOneTupleOneTable("revue", $id, $revue);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function insertCommandeDocument(?array $champs) : ?int{
        if (
            empty($champs) ||
            !array_key_exists('id', $champs) ||
            !array_key_exists('dateCommande', $champs) ||
            !array_key_exists('montant', $champs) ||
            !array_key_exists('nbExemplaire', $champs) ||
            !array_key_exists('idLivreDvd', $champs)
        ) {
            return null;
        }

        $commande = array(
            "id" => $champs["id"],
            "dateCommande" => $champs["dateCommande"],
            "montant" => $champs["montant"],
            "idSuivi" => "00001"
        );

        $commandeDocument = array(
            "id" => $champs["id"],
            "nbExemplaire" => $champs["nbExemplaire"],
            "idLivreDvd" => $champs["idLivreDvd"]
        );

        $nb1 = $this->insertOneTupleOneTable("commande", $commande);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->insertOneTupleOneTable("commandedocument", $commandeDocument);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function updateCommandeDocument(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs)) {
            return null;
        }

        $ancienneCommande = $this->conn->queryBDD(
            "select c.idSuivi, cd.nbExemplaire, cd.idLivreDvd, c.dateCommande
             from commande c
             join commandedocument cd on cd.id = c.id
             where c.id = :id",
            array("id" => $id)
        );

        if (empty($ancienneCommande)) {
            return null;
        }

        $ancienne = $ancienneCommande[0];

        $commande = array(
            "dateCommande" => $champs["dateCommande"],
            "montant" => $champs["montant"],
            "idSuivi" => $champs["idSuivi"]
        );

        $commandeDocument = array(
            "nbExemplaire" => $champs["nbExemplaire"],
            "idLivreDvd" => $champs["idLivreDvd"]
        );

        $nb1 = $this->updateOneTupleOneTable("commande", $id, $commande);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->updateOneTupleOneTable("commandedocument", $id, $commandeDocument);
        if ($nb2 === null) {
            return null;
        }

        $nb3 = 0;
        if ($ancienne["idSuivi"] !== "00003" && $champs["idSuivi"] === "00003") {
            $nb3 = $this->creerExemplairesDepuisCommande(
                $champs["idLivreDvd"],
                intval($champs["nbExemplaire"]),
                $champs["dateCommande"]
            );
            if ($nb3 === null) {
                return null;
            }
        }

        return $nb1 + $nb2 + $nb3;
    }

    private function creerExemplairesDepuisCommande(string $idDocument, int $nbExemplaires, string $dateCommande) : ?int{
        $result = $this->conn->queryBDD(
            "select max(numero) as maxNumero from exemplaire where id = :id",
            array("id" => $idDocument)
        );

        $maxNumero = 0;
        if (!empty($result) && !is_null($result[0]["maxNumero"])) {
            $maxNumero = intval($result[0]["maxNumero"]);
        }

        $nbAjouts = 0;
        for ($i = 1; $i <= $nbExemplaires; $i++) {
            $exemplaire = array(
                "id" => $idDocument,
                "numero" => $maxNumero + $i,
                "dateAchat" => $dateCommande,
                "photo" => "",
                "idEtat" => "00001"
            );

            $nb = $this->insertOneTupleOneTable("exemplaire", $exemplaire);
            if ($nb === null) {
                return null;
            }
            $nbAjouts += $nb;
        }

        return $nbAjouts;
    }

    private function deleteCommandeDocument(?array $champs) : ?int{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array(
            "id" => $champs["id"]
        );

        $result = $this->conn->queryBDD(
            "select idSuivi from commande where id = :id",
            $parametres
        );

        if (empty($result)) {
            return null;
        }

        if ($result[0]["idSuivi"] === "00003" || $result[0]["idSuivi"] === "00004") {
            return null;
        }

        $nb1 = $this->conn->updateBDD("delete from commandedocument where id = :id", $parametres);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->conn->updateBDD("delete from commande where id = :id", $parametres);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function selectCommandesRevue(?array $champs) : ?array{
        if (empty($champs) || !array_key_exists('id', $champs)) {
            return null;
        }

        $parametres = array("id" => $champs["id"]);

        $requete = "select c.id, c.dateCommande, c.montant, cr.dateFinAbonnement, cr.idRevue ";
        $requete .= "from abonnement cr ";
        $requete .= "join commande c on c.id = cr.id ";
        $requete .= "where cr.idRevue = :id ";
        $requete .= "order by c.dateCommande desc";

        return $this->conn->queryBDD($requete, $parametres);
    }

    private function insertCommandeRevue(?array $champs) : ?int{
        if (
            empty($champs) ||
            !array_key_exists('id', $champs) ||
            !array_key_exists('dateCommande', $champs) ||
            !array_key_exists('montant', $champs) ||
            !array_key_exists('dateFinAbonnement', $champs) ||
            !array_key_exists('idRevue', $champs)
        ) {
            return null;
        }

        $commande = array(
            "id" => $champs["id"],
            "dateCommande" => $champs["dateCommande"],
            "montant" => $champs["montant"],
            "idSuivi" => "00001"
        );

        $commandeRevue = array(
            "id" => $champs["id"],
            "dateFinAbonnement" => $champs["dateFinAbonnement"],
            "idRevue" => $champs["idRevue"]
        );

        $nb1 = $this->insertOneTupleOneTable("commande", $commande);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->insertOneTupleOneTable("abonnement", $commandeRevue);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function deleteCommandeRevue(?array $champs) : ?int{
    if (empty($champs) || !array_key_exists('id', $champs)) {
        return null;
    }

    $parametres = array("id" => $champs["id"]);

    $result = $this->conn->queryBDD(
        "select count(*) as nb
         from exemplaire
         where idCommande = :id",
        $parametres
    );

    if (!empty($result) && intval($result[0]["nb"]) > 0) {
        return null;
    }

    $nb1 = $this->conn->updateBDD("delete from abonnement where id = :id", $parametres);
    if ($nb1 === null) {
        return null;
    }

    $nb2 = $this->conn->updateBDD("delete from commande where id = :id", $parametres);
    if ($nb2 === null) {
        return null;
    }

    return $nb1 + $nb2;
}

    private function updateCommandeRevue(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs)) {
            return null;
        }

        $commande = array(
            "dateCommande" => $champs["dateCommande"],
            "montant" => $champs["montant"]
        );

        $commandeRevue = array(
            "dateFinAbonnement" => $champs["dateFinAbonnement"],
            "idRevue" => $champs["idRevue"]
        );

        $nb1 = $this->updateOneTupleOneTable("commande", $id, $commande);
        if ($nb1 === null) {
            return null;
        }

        $nb2 = $this->updateOneTupleOneTable("abonnement", $id, $commandeRevue);
        if ($nb2 === null) {
            return null;
        }

        return $nb1 + $nb2;
    }

    private function selectCommandesRevueAExpirationProche() : ?array{
        $requete = "select r.id, d.titre, c.id as idCommande, cr.dateFinAbonnement ";
        $requete .= "from abonnement cr ";
        $requete .= "join commande c on c.id = cr.id ";
        $requete .= "join revue r on r.id = cr.idRevue ";
        $requete .= "join document d on d.id = r.id ";
        $requete .= "where cr.dateFinAbonnement between curdate() and date_add(curdate(), interval 30 day) ";
        $requete .= "order by cr.dateFinAbonnement asc";

        return $this->conn->queryBDD($requete);
    }

    private function updateExemplaire(?string $id, ?array $champs) : ?int{
        if (is_null($id) || empty($champs)) {
            return null;
        }

        if (!array_key_exists('numero', $champs)) {
            return null;
        }

        $requete = "update exemplaire
                    set dateAchat = :dateAchat,
                        photo = :photo,
                        idEtat = :idEtat
                    where id = :id and numero = :numero";

        $parametres = array(
            "id" => $champs["id"],
            "numero" => $champs["numero"],
            "dateAchat" => $champs["dateAchat"],
            "photo" => $champs["photo"],
            "idEtat" => $champs["idEtat"]
        );

        return $this->conn->updateBDD($requete, $parametres);
    }

   private function deleteExemplaire(?array $champs) : ?int{
        if (empty($champs) || !array_key_exists('id', $champs) || !array_key_exists('numero', $champs)) {
            return null;
        }

        $parametres = array(
            "id" => $champs["id"],
            "numero" => $champs["numero"]
        );

        $requete = "delete from exemplaire where id = :id and numero = :numero";
        return $this->conn->updateBDD($requete, $parametres);
    }
}
