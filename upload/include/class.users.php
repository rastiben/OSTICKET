<?php

require_once(INCLUDE_DIR . 'bdd.user.php');
require_once(INCLUDE_DIR . 'class.org.php');

class userCollection{

    /*
    *Liste des organisation
    */
    public $users = [];

    /*
    *Instance de la classe OrganisationCollection
    */
    private static $instance = null;

    /*
    *Objet base de données.
    */
    private $bdd_user = null;

    /*
    *Constructeur
    */
    private function __construct(){
        $this->bdd_user = bdd_user::getInstance();
    }

    /*
    *Création de l'objet bdd_org;
    */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
          self::$instance = new userCollection();
        }
        return self::$instance;
    }

    /*
    *Récupération des Organisation
    */
    public function lookUp(){
        $result = $this->bdd_user->getUsers();

        foreach($result as $key=>$user){
             $this->addUser($user);
        }

        return $this->getCollectionPage();
    }

    /*
    *Récupération d'un user
    */
    public function lookUpById($id,$offset=1){
        $result = $this->bdd_user->getUserById($id);

        foreach($result as $key=>$user){
             $this->addUser($user);
        }

        return $this->getCollectionPage($offset);
    }

    /*
    *Ajout d'un élément dans la collection
    */
    private function addUser($data){
        array_push($this->users,new UserC($data));
    }

    /*
    *Récupération des occurences à afficher
    */
    public function getCollectionPage($offset=null,$query=null){
        return $this->users;
    }

    /*
    *Mettre à jour la base user pour créer une utilisateur lambda pour chaque organisation
    */
    public function majBaseUser(){
        //Recup org
        $orgs = OrganisationCollection::getInstance();
        $orgs = $orgs->lookUp(null);

        foreach($orgs as $org){
            //Création d'un requete multiple. TEST Temps
            $this->bdd_user->addUser($org->getId(),'U'.$org->getName(),$org->getName());//org_id,name
        }
    }

    /*
    *Mettre à jour la base user pour créer une utilisateur lambda pour chaque organisation
    */
    public function setOrgName(){
        //Recup user
        $users = $this->lookUp();

        //test de présence dans la base
        //par défaut : U-411-CLIENT
        //si non création
        foreach($users as $user){
            //Création d'un requete multiple. TEST Temps
            //print_r($user->getName());
            $this->bdd_user->setOrgName($user->getOrgId(),strstr(1,$user->getName()));//org_id,name
        }
    }

}

//CT_Num,CT_Adresse,CT_Complement,CT_CodePostal,CT_Ville,CT_Telephone,CT_Site
class UserC{

    /*
    *Données relative à l'organisation
    */
    public $data = null;

    /*
    *Constructeur
    */
    public function __construct($data){
        $this->data = array_values($data);
    }

    /*
    *Récupération de l'id
    */
    public function getId(){
        return $this->data[0];
    }

    /*
    *Récupération du nom de l'organisation
    */
    public function getName(){
        return $this->data[1];
    }

    /*
    *Récupération de l'adresse de l'organisation
    */
    public function getOrgId(){
        return $this->data[2];
    }

    /*
    *Récupération de l'adresse de l'organisation
    */
    public function getOrgName(){
        return $this->data[3];
    }

}
//echo $org->getName();


?>
