<?php

require_once(INCLUDE_DIR . 'class.bdd.php');

class bdd_stock{

    /*
    *Objet base de données.
    */
    private $DB = null;

    /*
    *Instance de la classe BDD
    */
    private static $instance = null;

    /*
    *Constructeur
    */
    private function __construct(){
        $this->DB = BDD::getInstance();
    }

    /*
    *Création de l'objet bdd_org;
    */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
          self::$instance = new bdd_stock();
        }
        return self::$instance;
    }

    public function getStock($stock){
        $prepare = $this->DB->prepare("SELECT F_ARTSTOCK.AR_Ref,AS_QteSto,F_ARTICLE.AR_SuiviStock,F_ARTSTOCK.DE_No,AR_Design,AR_PrixVen
        FROM F_ARTSTOCK,F_DEPOT,F_ARTICLE
        WHERE  F_DEPOT.DE_No = F_ARTSTOCK.DE_No
        AND F_ARTICLE.AR_Ref = F_ARTSTOCK.AR_Ref
        AND F_DEPOT.DE_Intitule = ?
        AND F_ARTSTOCK.AS_QteSto > 0");
        $values = array($stock);
        $this->DB->execute($prepare,$values);
        return $prepare;
    }

    public function getSN($reference,$stock){
        $prepare = $this->DB->prepare("SELECT LS_NoSerie FROM F_LOTSERIE,F_DEPOT
        WHERE  AR_Ref = ?
        AND DE_Intitule = ?
        AND F_DEPOT.DE_No = F_LOTSERIE.DE_No
        AND LS_QteRestant > 0");
        $values = array($reference,$stock);
        $this->DB->execute($prepare,$values);
        return $prepare;
    }

    public function getStocks(){
      $prepare = $this->DB->prepare("SELECT DE_INTITULE FROM F_DEPOT");
      $values = array();
      $this->DB->execute($prepare,$values);
      return $prepare;
    }
}

?>
