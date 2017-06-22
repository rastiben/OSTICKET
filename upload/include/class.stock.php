<?php

require_once(INCLUDE_DIR . 'bdd.stock.php');
require_once(INCLUDE_DIR . 'class.article.php');

class stock{

    public $articles = [];

    private $bdd_stock = null;

    public function __construct($stock){
        $this->bdd_stock = bdd_stock::getInstance();
        $result = $this->bdd_stock->getStock($stock);

        while($myRow = odbc_fetch_array($result)){
            $this->addArticle($myRow);
        }

    }

    /*
    *Ajout d'un élément dans la collection
    */
    private function addArticle($data){
        array_push($this->articles,new Article($data));
    }

    /*
    *Récupération des numéros de series
    */
    public static function getSN($reference,$agent){
        $bdd_stock = bdd_stock::getInstance();

        $sn = $bdd_stock->getSN($reference,$agent);

        $serialNumbers = [];
        while($myRow = odbc_fetch_array($sn)){
            array_push($serialNumbers,$myRow['LS_NoSerie']);
        }

        return $serialNumbers;
    }

    /*
    *Récupération de tout les stocks
    */
    public static function getStocks(){
      $bdd_stock = bdd_stock::getInstance();
      $temp = $bdd_stock->getStocks();

      $stocks = [];
      while($myRow = odbc_fetch_array($temp)){
          array_push($stocks,$myRow['DE_INTITULE']);
      }
      return $stocks;
    }
}

?>
