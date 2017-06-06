<?php

class Atelier
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
            $this->dbh = new PDO('mysql:host=localhost;dbname=osticket', $this->username, $this->password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
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

    public function add_contenu($ticket_id,$type,$planche){

        $res = $this->dbh->prepare("INSERT INTO ost_atelier_planche_contenu (ticket_id,type_id,planche_id,etat_id)
        VALUES (:ticket_id,
        (SELECT id FROM ost_atelier_contenu_type WHERE type = :type),
        (SELECT id FROM ost_atelier_planche WHERE planche = :planche),
        1)");
        $res->execute(array(':ticket_id'=>$ticket_id,':type'=>$type,':planche'=>$planche));

        $lastContenuInsertedID = $this->dbh->lastInsertId();

        //ajout d'un VD et de la prepa.
        if($type == "prepa"){
            //Insertion du VD
            $res = $this->dbh->prepare("INSERT INTO ost_atelier_preparation_vd (id) VALUES (NULL)");
            $res->execute(array());
            $VD = $this->dbh->lastInsertId();

            //Insertion de la prepa
            $res = $this->dbh->prepare("INSERT INTO ost_atelier_preparation (id_contenu,id_VD) VALUES (:lastContenuInsertedID,:vd)");
            $res->execute(array(':lastContenuInsertedID'=>$lastContenuInsertedID,':vd'=>$VD));

        } else {

            $res = $this->dbh->prepare("INSERT INTO ost_atelier_reparation (id_contenu) VALUES (:lastContenuInsertedID)");
            $res->execute(array(':lastContenuInsertedID'=>$lastContenuInsertedID));

        }

        return array('id'=>$lastContenuInsertedID,'vd'=>isset($VD) ? $VD : null);
    }

    public function get_org_planches(){
        $res = $this->dbh->prepare("SELECT ost_ticket.ticket_id,ost_atelier_planche_contenu.planche_id
        FROM ost_ticket,ost_atelier_planche,ost_atelier_planche_contenu,ost_atelier_contenu_type
        WHERE ost_ticket.ticket_id = ost_atelier_planche_contenu.ticket_id
        AND ost_atelier_planche.id = ost_atelier_planche_contenu.planche_id
        AND ost_atelier_contenu_type.id = ost_atelier_planche_contenu.type_id");
        $res->execute();
        return $res->fetchAll();
    }

    public function addPrepaInfo($id_contenu,$modele,$etiquetage,$dossierSAV, $septZip, $acrobat, $flash, $java, $pdf, $autre, $type, $userAccount, $mdp, $activation, $uninstall, $maj, $register, $verifActivation, $divers){
        $res = $this->dbh->prepare("INSERT INTO ost_atelier_preparation (id_contenu, modele, etiquetage, dossierSAV, septZip, acrobat, flash, java, pdf, autre, type, userAccount, mdp, activation, uninstall, maj, register, verifActivation, divers)
        VALUES(:id_contenu,:modele,:etiquetage,:dossierSAV, :septZip, :acrobat, :flash, :java, :pdf, :autre, :type, :userAccount, :mdp, :activation, :uninstall, :maj, :register, :verifActivation, :divers)
        ON DUPLICATE KEY UPDATE
        modele = :modele,
        etiquetage = :etiquetage,
        dossierSAV = :dossierSAV,
        septZip = :septZip,
        acrobat = :acrobat,
        flash = :flash,
        java = :java,
        pdf = :pdf,
        autre = :autre,
        type = :type,
        userAccount = :userAccount,
        mdp = :mdp,
        activation = :activation,
        uninstall = :uninstall,
        maj = :maj,
        register = :register,
        verifActivation = :verifActivation,
        divers = :divers");
        $res->execute(array(':id_contenu'=>$id_contenu,
                            ':modele'=>$modele,
                            ':etiquetage'=>$etiquetage,
                            ':dossierSAV'=>$dossierSAV,
                            ':septZip'=>$septZip,
                            ':acrobat'=>$acrobat,
                            ':flash'=>$flash,
                            ':java'=>$java,
                            ':pdf'=>$pdf,
                            ':autre'=>$autre,
                            ':type'=>$type,
                            ':userAccount'=>$userAccount,
                            ':mdp'=>$mdp,
                            ':activation'=>$activation,
                            ':uninstall'=>$uninstall,
                            ':maj'=>$maj,
                            ':register'=>$register,
                            ':verifActivation'=>$verifActivation,
                            ':divers'=>$divers));
        return $res->fetchAll();
    }

    public function addRepaInfo($id_contenu,$marque,$model,$sn,$vd,$os,$motDePasse,$login,$office,$autreSoft){
        $res = $this->dbh->prepare("INSERT INTO ost_atelier_reparation(id_contenu,marque,model,sn,vd,os,motDePasse,login,office,autreSoft)
        VALUES(:id_contenu,:marque,:model,:sn,:vd,:os,:motDePasse,:login,:office,:autreSoft)
        ON DUPLICATE KEY UPDATE
        marque = :marque,
        model = :model,
        sn = :sn,
        vd = :vd,
        os = :os,
        motDePasse = :motDePasse,
        login = :login,
        office = :office,
        autreSoft = :autreSoft");
        $res->execute(array(':id_contenu'=>$id_contenu,
                            ':marque'=>$marque,
                            ':model'=>$model,
                            ':sn'=>$sn,
                            ':vd'=>$vd,
                            ':os'=>$os,
                            ':motDePasse'=>$motDePasse,
                            ':login'=>$login,
                            ':office'=>$office,
                            ':autreSoft'=>$autreSoft));
        return $res->fetchAll();
    }

    public function getPlanches(){
        $res = $this->dbh->prepare("SELECT ost_atelier_contenu_type.type as contenuType,ost_atelier_planche.planche,ost_atelier_contenu_etat.etat,ost_atelier_planche_contenu.id as numContenue,ost_ticket_priority.priority_desc as priority, ost_atelier_planche_contenu.ticket_id, ost_ticket.number, ost_user.org_id,ost_user.org_name, ost_atelier_preparation.*,ost_atelier_reparation.*,ost_atelier_preparation_vd.*
        FROM ost_atelier_planche_contenu
        INNER JOIN ost_atelier_contenu_type
        ON ost_atelier_contenu_type.id = ost_atelier_planche_contenu.type_id
        INNER JOIN ost_ticket
        ON ost_atelier_planche_contenu.ticket_id = ost_ticket.ticket_id
        INNER JOIN ost_user
        ON ost_user.id = ost_ticket.user_id
        INNER JOIN ost_ticket__cdata
        ON ost_ticket__cdata.ticket_id = ost_ticket.ticket_id
        INNER JOIN ost_ticket_priority
        ON ost_ticket__cdata.priority = ost_ticket_priority.priority_id
        INNER JOIN ost_atelier_contenu_etat
        ON ost_atelier_contenu_etat.id = ost_atelier_planche_contenu.etat_id
        LEFT JOIN ost_atelier_planche
        ON ost_atelier_planche.id = ost_atelier_planche_contenu.planche_id
        LEFT JOIN ost_atelier_preparation
        ON ost_atelier_planche_contenu.id = ost_atelier_preparation.id_contenu
        LEFT JOIN ost_atelier_preparation_vd
        ON ost_atelier_preparation.id_VD = ost_atelier_preparation_vd.id
        LEFT JOIN ost_atelier_reparation
        ON ost_atelier_planche_contenu.id = ost_atelier_reparation.id_contenu
        ORDER BY ticket_id,id_VD");
        $res->execute(array());
        /*print_r($res->fetchAll());*/
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function affectContenu($id,$planche){
        $res = $this->dbh->prepare("UPDATE ost_atelier_planche_contenu
        SET planche_id = (SELECT id FROM ost_atelier_planche WHERE planche = :planche)
        WHERE id = :id");
        $res->execute(array(':id'=>$id,':planche'=>$planche));
    }

    public function changeState($id,$etat){
        $etat = strtr(utf8_decode($etat),
        utf8_decode(
        'ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ'),
        'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy');
        echo $etat;
        $res = $this->dbh->prepare("UPDATE ost_atelier_planche_contenu
        SET etat_id = (SELECT id FROM ost_atelier_contenu_etat WHERE etat = :etat)
        WHERE id = :id");
        $res->execute(array(':id'=>$id,':etat'=>$etat));
    }

    public function updateVD($id,$client,$type,$numeroSerie,$versionWindows,$numLicenceW,$versionOffice,$numLicenceO,$garantie,$debutGarantie,$mail,$mdp){
        $res = $this->dbh->prepare("UPDATE ost_atelier_preparation_vd
        SET client = :client,
        type = :type,
        numeroSerie = :numeroSerie,
        versionWindows = :versionWindows,
        numLicenceW = :numLicenceW,
        versionOffice = :versionOffice,
        numLicenceO = :numLicenceO,
        garantie = :garantie,
        debutGarantie = :debutGarantie,
        mail = :mail,
        mdp = :mdp
        WHERE id = :id");
        $res->execute(array(':id'=>$id,':client'=>$client,':type'=>$type,':numeroSerie'=>$numeroSerie,':versionWindows'=>$versionWindows,':numLicenceW'=>$numLicenceW,':versionOffice'=>$versionOffice,':numLicenceO'=>$numLicenceO,':garantie'=>$garantie,':debutGarantie'=>$debutGarantie,':mail'=>$mail,':mdp'=>$mdp));
    }

    public function deleteContenu($id){
        $res = $this->dbh->prepare('DELETE FROM ost_atelier_planche_contenu WHERE id = :id');
        $res->execute(array(':id'=>$id));
    }

    public function getAtelierTicket($ticketID){
        $res = $this->dbh->prepare('SELECT ost_atelier_contenu_type.type as contenuType,ost_atelier_planche.planche,ost_atelier_contenu_etat.etat,ost_atelier_planche_contenu.id as numContenue, ost_ticket.number, ost_atelier_preparation.*,ost_atelier_reparation.*,ost_atelier_preparation_vd.*,ost_atelier_reparation_fs.type as typeFiche,ost_atelier_reparation_fs.accessoire as accessoireFiche
        FROM ost_atelier_planche_contenu
        INNER JOIN ost_atelier_contenu_type
        ON ost_atelier_contenu_type.id = ost_atelier_planche_contenu.type_id
        INNER JOIN ost_ticket
        ON ost_atelier_planche_contenu.ticket_id = ost_ticket.ticket_id
        INNER JOIN ost_atelier_contenu_etat
        ON ost_atelier_contenu_etat.id = ost_atelier_planche_contenu.etat_id
        LEFT JOIN ost_atelier_planche
        ON ost_atelier_planche.id = ost_atelier_planche_contenu.planche_id
        LEFT JOIN ost_atelier_preparation
        ON ost_atelier_planche_contenu.id = ost_atelier_preparation.id_contenu
        LEFT JOIN ost_atelier_preparation_vd
        ON ost_atelier_preparation.id_VD = ost_atelier_preparation_vd.id
        LEFT JOIN ost_atelier_reparation
        ON ost_atelier_planche_contenu.id = ost_atelier_reparation.id_contenu
        LEFT JOIN ost_atelier_reparation_fs
        ON ost_atelier_reparation_fs.id_repa = ost_atelier_planche_contenu.id
        WHERE ost_atelier_planche_contenu.ticket_id = :ticketID');
        $res->execute(array(':ticketID'=>$ticketID));
        return $res->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertOrUpdateFicheSuivi($repa_id,$type,$accessoire){
        $res = $this->dbh->prepare('INSERT INTO ost_atelier_reparation_fs VALUES(:id_repa,:type,:accessoire)
        ON DUPLICATE KEY UPDATE
        type = :type,
        accessoire = :accessoire');
        $res->execute(array(':id_repa'=>$repa_id,':type'=>$type,':accessoire'=>$accessoire));
    }

}

if(isset($_REQUEST['request'])){
    if($_REQUEST['request'] == 'addContenu'){
        echo json_encode(Atelier::getInstance()->add_contenu($_REQUEST['ticket_id'],$_REQUEST['type'],$_REQUEST['planche']));
    } else if($_REQUEST['request'] == 'addPrepaInfo'){
        Atelier::getInstance()->addPrepaInfo($_REQUEST['id_contenu'],
                                            $_REQUEST['modele'],
                                            $_REQUEST['etiquetage'],
                                            $_REQUEST['dossierSAV'],
                                            $_REQUEST['septZip'],
                                            $_REQUEST['acrobat'],
                                            $_REQUEST['flash'],
                                            $_REQUEST['java'],
                                            $_REQUEST['pdf'],
                                            $_REQUEST['autre'],
                                            $_REQUEST['type'],
                                            $_REQUEST['userAccount'],
                                            $_REQUEST['mdp'],
                                            $_REQUEST['activation'],
                                            $_REQUEST['uninstall'],
                                            $_REQUEST['maj'],
                                            $_REQUEST['register'],
                                            $_REQUEST['verifActivation'],
                                            $_REQUEST['divers']);
    } else if($_REQUEST['request'] == 'addRepaInfo'){
        Atelier::getInstance()->addRepaInfo($_REQUEST['id_contenu'],
                                            $_REQUEST['marque'],
                                            $_REQUEST['model'],
                                            $_REQUEST['sn'],
                                            $_REQUEST['vd'],
                                            $_REQUEST['os'],
                                            $_REQUEST['motDePasse'],
                                            $_REQUEST['login'],
                                            $_REQUEST['office'],
                                            $_REQUEST['autreSoft']);
    } else if($_REQUEST['request'] == 'getPlanches'){
        echo json_encode(Atelier::getInstance()->getPlanches());
    } else if($_REQUEST['request'] == 'affectContenu'){
        Atelier::getInstance()->affectContenu($_REQUEST['id'],$_REQUEST['planche']);
    } else if($_REQUEST['request'] == 'changeState'){
        Atelier::getInstance()->changeState($_REQUEST['id'],$_REQUEST['etat']);
    } else if($_REQUEST['request'] == "updateVD"){
         Atelier::getInstance()->updateVD($_REQUEST['id'],$_REQUEST['client'],$_REQUEST['type'],$_REQUEST['numeroSerie'],$_REQUEST['versionWindows'],$_REQUEST['numLicenceW'],$_REQUEST['versionOffice'],$_REQUEST['numLicenceO'],$_REQUEST['garantie'],$_REQUEST['debutGarantie'],$_REQUEST['mail'],$_REQUEST['mdp']);
    } else if($_REQUEST['request'] == "deleteContenu"){
        Atelier::getInstance()->deleteContenu($_REQUEST['id']);
    } else if($_REQUEST['request'] == "getAtelierTicket"){
        echo json_encode(Atelier::getInstance()->getAtelierTicket($_REQUEST['ticketID']));
    } else if($_REQUEST['request'] == 'insertOrUpdateFicheSuivi'){
        Atelier::getInstance()->insertOrUpdateFicheSuivi($_REQUEST['id_repa'],$_REQUEST['type'],$_REQUEST['accessoire']);
    }
}

?>
