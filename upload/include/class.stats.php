<?php
/*
*Classe Statistique.
*Une fonction pour chaque statistique.
*Initialisation à la connexion à la base de données dans le constructeur.
*/
class stats{

    public $hostname;
    public $username;
    public $password;
    public $dbh;

    /*
    *Constructeur.
    *Connexion à la base de données & initialisation des variables.
    */
    function __construct(){
        $this->hostname = 'localhost';
        $this->username = 'root';
        $this->password = '';
        try{
            $this->dbh = new PDO('mysql:host=localhost;dbname=osticket', $this->username, $this->password);
        }catch(PDOException $e){
            die($e);
        }
    }

    /*
    *Obtention du nombres de ticket ouverts & fermées pour une organisation données.
    *Retour du tableau en json.
    */
    function statsTicketFromOrg($org,$date1,$date2){
        $result = array();

        /*MOIS*/
        $mois = ["01"=>"Janvier",
                 "02"=>"Février",
                 "03"=>"Mars",
                 "04"=>"Avril",
                 "05"=>"Mai",
                 "06"=>"Juin",
                 "07"=>"Juillet",
                 "08"=>"Aout",
                 "09"=>"Septembre",
                 "10"=>"Octobre",
                 "11"=>"Novembre",
                 "12"=>"Décembre"];
        /*INITIALISATION DES DEUX DATES*/
        $begin    = (DateTime::createFromFormat('d/m/Y',$date1));
        $end      = (DateTime::createFromFormat('d/m/Y',$date2));
        $memeMoisAnnee = false;
        if($begin->format('m-Y') === $end->format('m-Y'))
            $memeMoisAnnee = true;

        //return json_encode($result);
        if($memeMoisAnnee){
            $result[1][0] = $begin->format('d') . '-' . $mois[$begin->format('m')] . ' au ' . $end->format('d') . '-' . $mois[$end->format('m')] . ' ' . $begin->format('Y');
            //Premier requete
            $result[2][0] = $this->getTicketOpenFromOrg($org,$begin->format('d-m-Y'),$end->format('d-m-Y'));
            //Deuxieme Requete
            $result[3][0] = $this->getTicketClosedFromOrg($org,$begin->format('d-m-Y'),$end->format('d-m-Y'));
        }
        else{
            $i = 0;
            $result[1][$i] = $begin->format('d') . '-' . $mois[$begin->format('m')] . '-' . $begin->format('Y');
            //$i += 1;
            while ($begin < $end) {
                if($i > 0)
                    $result[1][$i] = $mois[$begin->format('m')];
                //Si l'année est différente
                $start = $begin->format('d-m-Y');
                $finish = $begin->modify('last day of this month')->format('d-m-Y');
                if(($begin->format('Y') !== $end->format('Y'))){
                    //Premier requete
                    $result[2][$i] = $this->getTicketOpenFromOrg($org,$start,$finish);
                    //Deuxieme Requete
                    $result[3][$i] = $this->getTicketClosedFromOrg($org,$start,$finish);
                }
                //Si l'année est la même mais que le mois est différent
                else if($begin->format('m') !== $end->format('m')){
                    //Premier requete
                    $result[2][$i] = $this->getTicketOpenFromOrg($org,$start,$finish);
                    //Deuxieme Requete
                    $result[3][$i] = $this->getTicketClosedFromOrg($org,$start,$finish);
                }
                //Sinon on stop la boucle
                else{
                    break;
                }
                //On passe au mois suivant
                $begin->modify('first day of next month');
                $i += 1;
            }
            //array_pop($result[1]);
            $result[1][$i] = $end->format('d') . '-' . $mois[$end->format('m')] . '-' . $end->format('Y');
            //Premier requete
            $result[2][$i] = $this->getTicketOpenFromOrg($org,$end->format('Y-m-01'),$end->format('d-m-Y'));
            //Deuxieme Requete
            $result[3][$i] = $this->getTicketClosedFromOrg($org,$end->format('Y-m-01'),$end->format('d-m-Y'));
        }

        //echo print_r($result);
        return json_encode($result);
    }

    /*
    *Obtention du nombres de ticket ouverts & fermées pour une organisation données.
    *Retour du tableau en json.
    */
    function statsTicketForAgent($agent,$date1,$date2){
        $result = array();
        /*MOIS*/
        $mois = ["01"=>"Janvier",
                 "02"=>"Février",
                 "03"=>"Mars",
                 "04"=>"Avril",
                 "05"=>"Mai",
                 "06"=>"Juin",
                 "07"=>"Juillet",
                 "08"=>"Aout",
                 "09"=>"Septembre",
                 "10"=>"Octobre",
                 "11"=>"Novembre",
                 "12"=>"Decembre"];
        /*INITIALISATION DES DEUX DATES*/
        $begin    = (DateTime::createFromFormat('d/m/Y',$date1));
        $end      = (DateTime::createFromFormat('d/m/Y',$date2));
        $memeMoisAnnee = false;
        if($begin->format('m-Y') === $end->format('m-Y'))
            $memeMoisAnnee = true;

        //return json_encode($result);
        if($memeMoisAnnee){
            $result[1][0] = $begin->format('d') . '-' . $mois[$begin->format('m')] . ' au ' . $end->format('d') . '-' . $mois[$end->format('m')] . ' ' . $begin->format('Y');
            //Premier requete
            $result[2][0] = $this->getTicketClosedForAgent($agent,$begin->format('d-m-Y'),$end->format('d-m-Y'));
        }
        else{
            $i = 0;
            $result[1][$i] = $begin->format('d') . '-' . $mois[$begin->format('m')] . '-' . $begin->format('Y');

            while ($begin < $end) {
                if($i > 0)
                    $result[1][$i] = $mois[$begin->format('m')];
                //Si l'année est différente
                $start = $begin->format('d-m-Y');
                $finish = $begin->modify('last day of this month')->format('d-m-Y');
                if(($begin->format('Y') !== $end->format('Y'))){
                    //Premier requete
                    $result[2][$i] = $this->getTicketClosedForAgent($agent,$start,$finish);
                }
                //Si l'année est la même mais que le mois est différent
                else if($begin->format('m') !== $end->format('m')){
                    //Premier requete
                    $result[2][$i] = $this->getTicketClosedForAgent($agent,$start,$finish);
                }
                //Sinon on stop la boucle
                else{
                    break;
                }
                //On passe au mois suivant
                $begin->modify('first day of next month');
                $i += 1;
            }
            $result[1][$i] = $end->format('d') . '-' . $mois[$end->format('m')] . '-' . $end->format('Y');
            //Premier requete
            $result[2][$i] = $this->getTicketClosedForAgent($agent,$end->format('Y-m-01'),$end->format('d-m-Y'));
        }

        //echo print_r($result);
        return json_encode($result);
    }

    /*
    *Obtention des différentes organisations
    */
    function getOrg(){
        $result = array();

        $res = $this->dbh->prepare("SELECT DISTINCT id,name FROM ost_organization");
        $res->execute();
        //$result[1][$i] = $res->fetchAll()[0];

        foreach($res->fetchAll() as $key=>$value){

            array_push($result,[$value["id"],$value["name"]]);
        }

        //print_r($result);
        return json_encode($result);
    }

    /*
    *Obtention des différents agents
    */
    function getAgent(){
        $result = array();

        $res = $this->dbh->prepare("SELECT DISTINCT staff_id,firstname,lastname FROM ost_staff");
        $res->execute();
        //$result[1][$i] = $res->fetchAll()[0];

        foreach($res->fetchAll() as $key=>$value){

            array_push($result,[$value["staff_id"],$value["firstname"] . " " . $value["lastname"]]);
        }

        //print_r($result);
        return json_encode($result);
    }

    /*
    *getTicketOpenFromOrg
    */
    function getTicketOpenFromOrg($org,$date1,$date2){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_user WHERE ost_ticket.user_id = ost_user.id AND ost_user.org_name LIKE :orgId AND ost_ticket.created >= :sDate AND ost_ticket.created <= :eDate");
        $res->execute(array(':orgId' => $org, ':sDate' => $date1, ':eDate' => $date2));
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    /*
    *getTicketClosedFromOrg
    */
    function getTicketClosedFromOrg($org,$date1,$date2){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_user WHERE ost_ticket.user_id = ost_user.id AND ost_user.org_name LIKE :orgId AND ost_ticket.created >= :date1 AND ost_ticket.created <= :date2 AND ost_ticket.closed IS NOT NULL");
        $res->execute(array(':orgId' => $org, ':date1' => $date1, ':date2' => $date2));
        return $res->fetchAll()[0]['COUNT(*)'];
    }

    /*
    *getTicketClosedFromOrg
    */
    function getTicketClosedForAgent($agent,$date1,$date2){
        $res = $this->dbh->prepare("SELECT COUNT(*) FROM ost_ticket,ost_staff WHERE ost_ticket.staff_id = ost_staff.staff_id AND ost_ticket.closed IS NOT NULL AND ost_ticket.closed >= :date1 AND ost_ticket.closed <= :date2 AND ost_ticket.staff_id = :agent");
        $res->execute(array(':agent' => $agent, ':date1' => $date1, ':date2' => $date2));
        return $res->fetchAll()[0]['COUNT(*)'];
    }


}

?>
