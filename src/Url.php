<?php
require '../vendor/autoload.php';
use Dotenv\Dotenv;

include_once("Connexion.php");

/**
 * Singleton car la récupération des données ne peut se faire qu'une fois
 * Permet de gérer le contenu de l'URL qui sollicite l'API
 */
class Url {
    
    
    /**
     * instance de la classe actuelle
     * @var Url
     */
    private static $instance = null;
    
    /**
     * accès aux variables d'environnement
     * @var Dotenv
     */
    private $dotenv;
    
    /**
     * tableau contenant toutes les variables transmises
     * @var array
     */
    private $data = [];
    
    /**
     * méthode de transfert HTTP utilisé pour accéder à l'API
     * (GET, PUT, POST, DELETE)
     * @var string
     */
    private $methodeHTTP;

    /**
     * utilisateur authentifié
     * @var array|null
     */
    private $utilisateurConnecte = null;
    
    /**
     * constructeur privé
     * récupère les variables d'environnement
     * récupère les variables envoyées via l'url
     */
    private function __construct() {
        // variables d'environnement
        $this->dotenv = Dotenv::createImmutable(__DIR__);
        $this->dotenv->load();
        // variables envoyées par l'url
        $this->data = $this->recupAllData();
    }

    /**
     * méthode statique de création de l'instance unique
     * @return Url
     */
    public static function getInstance() : Url{
        if(self::$instance === null){
            self::$instance = new Url();
        }
        return self::$instance;
    }

    /**
     * récupère la méthode HTTP utilisée pour le transfert
     * @return string
     */
    public function recupMethodeHTTP() : string{
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }  
  
    /**
     * retour d'une variable avec les caractères spéciaux convertis
     * et au format array si format "json" reçu
     * possibilité d'ajouter d'autres 'case' de conversions
     * @param string $nom
     * @param string $format
     * @return string|array|null
     */
    public function recupVariable(string $nom, string $format="string") : string|array|null{
        $variable = $this->data[$nom] ?? '';
        switch ($format){
            case "json" : 
                $variable = $variable ? json_decode($variable, true) : null;
                break;
        }
        return $variable;
    }    
 
    /**
     * vérifie l'authentification suivant la demande
     * possibilité d'ajouter des 'case' et de nouvelles fonctions 
     * si besoin d'un autre type d'authentification
     * @return bool
     */
    public function authentification(): bool{
        $authentification = htmlspecialchars($_ENV['AUTHENTIFICATION'] ?? '');
        switch ($authentification){
            case '' : return true ;
            case 'basic' : return $this->basicAuthentification() ;
            default : return true;
        }
    }

    /**
     * retourne l'utilisateur authentifié
     * @return array|null
     */
    public function getUtilisateurConnecte() : ?array{
        return $this->utilisateurConnecte;
    }

    /**
     * compare le user/pwd reçu en 'basic auth' 
     * avec le user/pwd dans la table utilisateur
     * @return bool true si authentification réussie
     */
    private function basicAuthentification() : bool{
        // récupère les variables envoyées en 'basic auth'
        $authUser = htmlspecialchars($_SERVER['PHP_AUTH_USER'] ?? '');
        $authPw = htmlspecialchars($_SERVER['PHP_AUTH_PW'] ?? '');

        if ($authUser === '' || $authPw === '') {
            return false;
        }

        try{
            // récupération des variables d'environnement de l'accès à la BDD
            $login = htmlspecialchars($_ENV['BDD_LOGIN'] ?? '');
            $pwd = htmlspecialchars($_ENV['BDD_PWD'] ?? '');
            $bd = htmlspecialchars($_ENV['BDD_BD'] ?? '');
            $server = htmlspecialchars($_ENV['BDD_SERVER'] ?? '');
            $port = htmlspecialchars($_ENV['BDD_PORT'] ?? '');

            // création / récupération de la connexion
            $conn = Connexion::getInstance($login, $pwd, $bd, $server, $port);

            // contrôle dans la base de données
            $requete = "select u.id, u.login, u.idService, s.libelle as service
                        from utilisateur u
                        join service s on s.id = u.idService
                        where u.login = :login and u.pwd = :pwd;";

            $param = array(
                "login" => $authUser,
                "pwd" => $authPw
            );

            $result = $conn->queryBDD($requete, $param);

            if ($result !== null && count($result) === 1) {
                $this->utilisateurConnecte = $result[0];
                return true;
            }
            return false;
        }catch(Exception $e){
            return false;
        }
    }    
 
    /**
     * récupération de toutes les variables envoyées par l'URL
     * nettoyage et retour dans un tableau associatif
     * @return array
     */
    private function recupAllData() : array{
        $data = [];
        if(!empty($_GET)){
            $data = array_merge($data, $_GET);
        }
        if(!empty($_POST)){
            $data = array_merge($data, $_POST);
        }
        $input = file_get_contents('php://input');
        parse_str($input, $postData);
        $data = array_merge($data, $postData);    
        // htmlspeciachars appliqué à chaque valeur du tableau
        $data = array_map(function($value) {
            return htmlspecialchars($value, ENT_NOQUOTES);
        }, $data);
        return $data;
    }

}
