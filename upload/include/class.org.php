<?php

require_once(INCLUDE_DIR . 'bdd.org.php');

class Pagination{

    public function __construct($total,$nb){
        $this->total = $total;
        $this->nbPerPage = $nb;
        $this->nbPage = ceil($this->total/$this->nbPerPage);
    }

    private function createPagination($page){
        $result = "";

        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        if(isset($_REQUEST['query'])){
            $query = "&query=" . $_REQUEST['query'];
        }

        if($page > 1){
            //begin
            $result .= "<a href=\"http://".$_SERVER['HTTP_HOST'].$uri_parts[0]."?p=".(1).$query."\">&lt;&lt;</a>&nbsp";
            //start
            $result .= "<a href=\"http://".$_SERVER['HTTP_HOST'].$uri_parts[0]."?p=".($page-1).$query."\">&lt;</a>&nbsp";
        }

        $result .= "<select onchange=\"location = this.value;\">";

        for($i = 1;$i<=$this->nbPage;$i++){
            $result .= "<option ". (($i == $page) ? "selected" : "") ." value=\"". "http://".$_SERVER['HTTP_HOST'].$uri_parts[0]."?p=".($i).$query."\" >". $i ."</option>";
        }

        $result .= "</select>&nbsp";

        if($page < $this->nbPage){
            //after
            $result .= "<a href=\"http://".$_SERVER['HTTP_HOST'].$uri_parts[0]."?p=".($page+1).$query."\">&gt;</a>&nbsp";
            //end
            $result .= "<a href=\"http://".$_SERVER['HTTP_HOST'].$uri_parts[0]."?p=".($this->nbPage).$query."\">&gt;&gt;</a>";
        }

        return $result;

    }

    public function paginate($page=1){
        return $this->createPagination($page);
    }

}

class OrganisationCollection{

    /*
    *Liste des organisation
    */
    public $orgs = [];

    /*
    *Instance de la classe OrganisationCollection
    */
    private static $instance = null;

    /*
    *Objet base de données.
    */
    private $bdd_org = null;

    /*
    *Constructeur
    */
    private function __construct(){
        $this->bdd_org = bdd_org::getInstance();
    }

    /*
    *Création de l'objet bdd_org;
    */
    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
          self::$instance = new OrganisationCollection();
        }
        return self::$instance;
    }

    public function toto(){
        $result = $this->bdd_org->toto();
        echo "<table style='text-align:left'><thread><th>Référence</th><th>Quantité</th></thead><tbody>";
        while($myRow = odbc_fetch_array($result)){
            echo "<tr>";
            foreach($myRow as $column){
                echo "<td>".$column."</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    /*Récupération des contrats*/
    public function getContrats(){
      $result = $this->bdd_org->getContrats();

      while($myRow = odbc_fetch_array($result)){
        //die();
        $contrats[] = $myRow['AR_REF'];
      }

      return $contrats;
    }

    /*
    *Récupération des Organisation
    */
    public function lookUp($offset=1){

        /*if($offset == null)
            $result = $this->bdd_org->getOrgs(null);
        else*/

        $result = $this->bdd_org->getOrgs($offset);

        while($myRow = odbc_fetch_array($result)){
            $this->addOrg($myRow);
        }

        /*if($offset == null)
            return $this->getCollectionPage($offset,null);
        else*/
        return $this->getCollectionPage($offset,1);
    }

    /*
    *Récupération des Organisation
    */
    public function lookUpById($query,$offset=1){
        $result = $this->bdd_org->getOrgWithId($query);

        while($myRow = odbc_fetch_array($result)){
            $this->addOrg($myRow);
        }

        return $this->getCollectionPage($offset,$query);
    }

    /*
    *Recherche par nom sans utilisation de classe
    */
    public function searchByName($query){
        $result = $this->bdd_org->getOrgWithName($query);

        while($myRow = odbc_fetch_array($result)){
          
            $this->addOrg($myRow);
        }

        return $this->getCollectionPage();
    }

    /*
    *Récupération des Organisation
    */
    public function lookUpByName($query,$offset=1){
        $result = $this->bdd_org->getOrgWithName($query);

        while($myRow = odbc_fetch_array($result)){
            $this->addOrg($myRow);
        }

        return $this->getCollectionPage($offset,$query);
    }

    public function findOneOccur($query){
      $result = $this->bdd_org->findOneOccur($query);

      $row = odbc_fetch_array($result);
      $this->addOrg($row);

      return $this->orgs;
    }

    /*
    *Ajout d'un élément dans la collection
    */
    private function addOrg($data){
        array_push($this->orgs,new Organisation($data));
    }

    /*
    *Récupération des occurences à afficher
    */
    public function getCollectionPage($offset=null,$query=null){
        if(empty($query) || !empty($offset)){
            return $this->orgs;
        } else {
            return array_splice($this->orgs,(50*($offset-1)),49);
        }
    }

    /*
    *Retourne le nombre d'organisation retounée
    */
    public function nbOrg($search){
        return array_values(odbc_fetch_array($this->bdd_org->nbOrg($search)))[0];
    }
}
//CT_Num,CT_Adresse,CT_Complement,CT_CodePostal,CT_Ville,CT_Telephone,CT_Site
class Organisation{

    /*
    *Données relative à l'organisation
    */
    public $data = null;

    /*
    *Constructeur
    */
    public function __construct($data){
        $this->data = array_values($data);
        for($i=0;$i<count($this->data);$i++){
            $this->data[$i] = iconv('Windows-1250', 'UTF-8', $this->data[$i]);
        }
    }

    /*
    *Récupération de l'id
    */
    /*public function getId(){
        return $this->data[0];
    }*/

    /*
    *Récupération du nom de l'organisation
    */
    public function getName(){
        return $this->data[0];
    }

    /*
    *Récupération de l'adresse de l'organisation
    */
    public function getAddress(){
        return $this->data[1];
    }

    /*
    *Récupération de l'adresse de l'organisation
    */
    public function getComplement(){
        return $this->data[2];
    }

    /*
    *Récupération du Code Postal
    */
    public function getCP(){
        return $this->data[3];
    }

    /*
    *Récupération de la Ville
    */
    public function getCity(){
        return $this->data[4];
    }

    /*
    *Récupération du numéro de téléphone
    */
    public function getPhone(){
        return $this->data[5];
    }

    /*
    *Récupération du site web
    */
    public function getWebSite(){
        return $this->data[6];
    }

}
//echo $org->getName();


?>
