<?php

//require_once('../../main.inc.php');
//require_once('../../include/class.staff.php');
//require_once('../include/class.csrf.php');

//$thisstaff = StaffAuthenticationBackend::getUser();

/*
*Classe Statistique.
*Une fonction pour chaque statistique.
*Initialisation à la connexion à la base de données dans le constructeur.
*/
class Rapport
{
    private static $instance;
    private $hostname;
    private $username;
    private $password;
    private $dbh;

    private function __construct()
    {
    // Your "heavy" initialization stuff here
        $this->hostname = 'localhost';
        $this->username = 'root';
        $this->password = '';
        try{
            $this->dbh = new PDO('mysql:host=localhost;dbname=osticket', $this->username, $this->password);
        }catch(PDOException $e){
            die($e);
        }
    }

    public static function getInstance()
    {
        if ( is_null( self::$instance ) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getRapportsHoraires($RapportID){
        $res = $this->dbh->prepare("SELECT id,arrive_inter,depart_inter,comment FROM ost_rapport_horaires WHERE id_rapport = :rapport_id");
        $res->execute(array(':rapport_id'=>$RapportID));

        return $res->fetchAll();
    }

    public function getRapportStock($RapportID){
        $res = $this->dbh->prepare("SELECT * FROM ost_rapport_stock WHERE id_rapport = :rapport_id");
        $res->execute(array(':rapport_id'=>$RapportID));

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRapports($ticketID){
        $res = $this->dbh->prepare("SELECT id,date_rapport,date_inter,firstname,lastname,contrat,instal,topic,couleur FROM ost_rapport,ost_staff,ost_help_topic WHERE ost_rapport.id_agent = ost_staff.staff_id AND ost_help_topic.topic_id = ost_rapport.topic_id AND id_ticket = :ticketID");
        $res->execute(array(':ticketID'=>$ticketID));

        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRapport($id){
        $res = $this->dbh->prepare("SELECT id,date_rapport,date_inter,firstname,lastname,contrat,instal FROM ost_rapport,ost_staff WHERE ost_rapport.id_agent = ost_staff.staff_id AND id = :ID");
        $res->execute(array(':ID'=>$id));
        return $res->fetchAll();
    }

    public function addHoraires($ticketId,$agentId,$rapportID,$topic_id,$dateInter,$arriveInter,$departInter,$symptomesObservations,$contrat,$instal,$sortieStock)
    {
        $date = DateTime::createFromFormat('d/m/Y', $dateInter);

        $arrive = DateTime::createFromFormat('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $arriveInter);
        $depart = DateTime::createFromFormat('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $departInter);

        //echo 'toto';

        if(empty($rapportID)){

            $date_rapport = date('Y-m-d');

            $res = $this->dbh->prepare("INSERT INTO ost_rapport (id_ticket,id_agent,date_rapport,date_inter,topic_id,contrat,instal) VALUES (:ticket_id,:id_agent,:date_rapport,:date_inter,:topic_id,:contrat,:instal)");

            $res->execute(array(':ticket_id'=>$ticketId,':id_agent'=>$agentId,':date_rapport'=>$date_rapport,':date_inter'=>$date->format('Y-m-d'),':topic_id'=>$topic_id,':contrat'=>$contrat,':instal'=>$instal));

            $rapportID = $this->dbh->lastInsertId();
        }

        //Ajout des sorties de stock
        $sortieStock = json_decode($sortieStock);
        foreach($sortieStock as $key=>$article){
            $res = $this->dbh->prepare("INSERT INTO ost_rapport_stock (id_rapport,reference,quantite,prix) VALUES (:id_rapport,:reference,:quantite,:prix)");

            $res->execute(array(':id_rapport'=>$rapportID,':reference'=>$article->reference,':quantite'=>$article->quantite,':prix'=>$article->prix));
        }

        $res = $this->dbh->prepare("INSERT INTO ost_rapport_horaires (id_rapport,arrive_inter,depart_inter,comment) VALUES (:rapport_id,:arrive_inter,:depart_inter,:comment)");
        $res->execute(array(':rapport_id'=>$rapportID,':arrive_inter'=>$arrive->format('Y-m-d H:i:s'),':depart_inter'=>$depart->format('Y-m-d H:i:s'),':comment'=>$symptomesObservations));

    }

    public function updateHoraire($horaireID,$dateInter,$arriveInter,$departInter,$symptomesObservations)
    {
        $date = DateTime::createFromFormat('d/m/Y', $dateInter);

        $arrive = DateTime::createFromFormat('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $arriveInter);
        $depart = DateTime::createFromFormat('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $departInter);

        $res = $this->dbh->prepare("UPDATE ost_rapport_horaires SET arrive_inter = :arrive_inter, depart_inter = :depart_inter, comment = :comment WHERE id = :horaireID");
        $res->execute(array(':arrive_inter'=>$arrive->format('Y-m-d H:i:s'),':depart_inter'=>$depart->format('Y-m-d H:i:s'),':comment'=>$symptomesObservations,':horaireID'=>$horaireID));

    }

}

if(isset($_POST['request'])){
    if($_POST['request'] == 'addHoraires'){
        Rapport::getInstance()->addHoraires($_POST['ticket_id'],$_POST['agent_id'],$_POST['rapport_id'],$_POST['topic_id'],$_POST['date_inter'],$_POST['arrive_inter'],$_POST['depart_inter'],$_POST['symptomesObservations'],$_POST['contrat'],$_POST['instal'],$_POST['sortieStock']);
    } else if($_POST['request'] == 'updateHoraire'){
        Rapport::getInstance()->updateHoraire($_POST['horaire_id'],$_POST['date_inter'],$_POST['arrive_inter'],$_POST['depart_inter'],$_POST['symptomesObservations']);
    } else if ($_POST['request'] == 'getRapports'){
        echo json_encode(Rapport::getInstance()->getRapports($_POST['ticketID']));
    } else if($_POST['request'] == 'getRapportsHoraires'){
        echo json_encode(Rapport::getInstance()->getRapportsHoraires($_POST['rapportID']));
    } else if($_POST['request'] == 'getRapportStock'){
        echo json_encode(Rapport::getInstance()->getRapportStock($_POST['rapportID']));
    }
}


?>
