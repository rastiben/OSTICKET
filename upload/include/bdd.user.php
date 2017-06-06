<?php

class bdd_user{

    /*
    *Instance de la classe BDD
    */
    private static $instance = null;

    /*
    *Private bdd
    */
    private $bdd = null;

    /*
    *Constructeur
    */
    private function __construct(){
        try {
            $this->bdd = new PDO('mysql:host=localhost;dbname=osticket', 'root', '');
        } catch (PDOException $e) {
            print "Erreur !: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /*
    *Création de l'objet bdd_org;
    */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
          self::$instance = new bdd_user();
        }
        return self::$instance;
    }

    /*
    *Range
    */
    private function getRange($offset){
        $between = (50*($offset-1)) + 49;
        return [(50*($offset-1)),$between];
    }

    public function prepare($query){
        return $this->bdd->prepare($query);
    }

    public function execute($prepare,$values){
        $prepare->execute($values);
        return $prepare->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    *Récupération des users
    */
    public function getUsers(){
        $prepare = $this->prepare("SELECT id,name,org_id FROM ost_user");
        return $this->execute($prepare,array());
    }

    public function getUserById($id){
        $prepare = $this->prepare("SELECT id,name,org_id,org_name FROM ost_user WHERE id = :id");
        return $this->execute($prepare,array(":id"=>$id));
    }

    public function addUser($org_id,$name,$org_name){
        $today = date('Y-m-d H:i:s');

        /*INSERT INTO ost_user (org_id,name,org_name,created,updated)
                                    SELECT :org_id,:name,:org_name,:created,:updated FROM DUAL
                                    WHERE NOT EXISTS (SELECT * FROM ost_user
                                          WHERE org_id=:org_id)
                                    LIMIT 1 */

        $prepare = $this->prepare('INSERT INTO ost_user (org_id,name,org_name,created,updated)
                                   VALUES (:org_id,:name,:org_name,:created,:updated)');
        return $this->execute($prepare,array(":org_id"=>$org_id,":name"=>$name,":org_name"=>$org_name,":created"=>$today,":updated"=>$today));
    }

    public function setOrgName($org_id,$name){
        $prepare = $this->prepare('UPDATE ost_user SET org_name = :name WHERE org_name IS NULL AND org_id = :org_id');
        return $this->execute($prepare,array(":org_id"=>$org_id,":name"=>$name));
    }

    public function updateUser($org_id,$name){
        $today = date('Y-m-d H:i:s');
        $prepare = $this->prepare("UPDATE ost_user SET org_id = :org_id,name = :name,updated = :updated");
        return $this->execute($prepare,array(":org_id"=>$org_id,":name"=>$name,":updated"=>$today));
    }
}

?>
