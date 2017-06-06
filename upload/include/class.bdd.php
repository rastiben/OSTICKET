<?php

class BDD{

    /*
    *Constante : Nom d'utilisateur de la BDD
    */
    const USERNAME = '';
    /*
    *Constante : Mot de passe de la BDD
    */
    const PASSWORD = '';
    /*
    *Instance de la classe BDD
    */
    private static $instance = null;
    /*
    *Instance de la base de données
    */
    public $ODBC = null;
    public $SAGE = null;

    /*SWITCH ENTRE DEUX LIENS ODBC (Moins de dev)
    Pouvoir récupérer l'id de l'organisation (A favoriser en prog)
    Ou alors se servir du nom de l'organisation ??'*/

    /*
    *CONSTRUCTEUR
    */
    private function __construct($odbc = false,$sage = false){
        try{
            if($odbc){
              $this->ODBC = odbc_connect('DSN=srvsage;server=srvsage;', '', '');
            }
            else if($sage){
              $this->SAGE = odbc_connect('sage', '', '');
            }
            else{
              $this->ODBC = odbc_connect('DSN=srvsage;server=srvsage;', '', '');
              $this->SAGE = odbc_connect('sage', '', '');
            }
        }catch(PDOException $e){
            die($e);
        }
    }

    /*
    *Création de l'objet BDD;
    */
    public static function getInstance($odbc = false,$sage = false)
    {
        if(is_null(self::$instance))
        {
          self::$instance = new BDD($odbc,$sage);
        }
        return self::$instance;
    }

    /*
    *Execution d'une requete
    */
    public function execute($prepare,$values){
        return odbc_execute($prepare,$values);
    }

    /*
    *Préparation d'une requete
    */
    public function prepare($query){
        return odbc_prepare($this->ODBC,$query);
    }

    /*
    *Préparation d'une requete sql
    */
    public function prepareSAGE($query){
        return odbc_prepare($this->SAGE,$query);
    }

    /*
    *Get last error
    */
    public function lastError(){
        return odbc_error();
    }

    /*
    *Création d'une requete select
    */
    public function selectBetween($table,$fields,$clauses="",$orderBy="",$range){
        $listField = "";

        foreach($fields as $key=>$field){
            $listField .= $field . ",";
            /*if(count($fields) > ($key+1))
                $listField .= ",";*/
        }

        $listClause = "";
        foreach($clauses as $key=>$clause){
            foreach($clause as $def){
                $listClause .= $def . " ";
            }
            if(count($clauses) > ($key+1))
                $listClause .= "AND ";
        }

        return "WITH OrderedOrg AS (SELECT ". $listField ."ROW_NUMBER() OVER (ORDER BY ". $orderBy .") AS 'RowNumber' FROM ". $table ." WHERE ". $listClause .") SELECT * FROM OrderedOrg WHERE RowNumber BETWEEN ".$range[0]." AND ".$range[1].";";

        //return "SELECT " . $listField . " FROM " . $table . " WHERE " . $listClause . " ORDER BY " . $orderBy;
    }

}

?>
