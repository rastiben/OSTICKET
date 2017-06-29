<?php

require_once(INCLUDE_DIR.'bdd.docSage.php');

class docSage{

    public static function getBDD(){
        $BDD = bdd_docSage::getInstance();
        return $BDD;
    }

    public static function createDocument($org,$stock,$lines,$typeSortie){
        //Pour chaque ligne
        $BDD = self::getBDD();

        //Récupération du lieu de livraison
        $LI_NO = $BDD->getLi_NO($org);
        $LI_NO = odbc_fetch_array($LI_NO)['LI_No'];

        switch($typeSortie){
          case "F":
            $typeSortie = "Facturable";
            break;
          case "O" :
            $typeSortie = "Offert";
            break;
          case "P":
            $typeSortie = "Prêt";
            break;
        }

        //Création de l'entete
        do{
            $DO_PIECE = self::getDoPiece($BDD);
            $ENTETE = $BDD->createDocEntete($org,$stock,$LI_NO,$DO_PIECE,$typeSortie);
        } while ($ENTETE === false);

        $lines = json_decode($lines);
        $ligne = 1000;

        //Ajout des lignes choisi
        foreach($lines as $key=>$line){
            if($line->suiviStock == 1){
                 //Pour chaque numéro de série
                 foreach($line->sn as $key=>$sn){
                     $BDD->createDocLineSN($org,$DO_PIECE,$ligne,$line->reference,$line->stock,$line->designation,$line->prix,$sn);
                     $ligne += 1000;
                 }
            } else {
                $BDD->createDocLineCMUP($org,$DO_PIECE,$ligne,$line->reference,$line->quantite,$line->stock,$line->designation,$line->prix);
                $ligne += 1000;
            }
        }
        //$BDD = self::getBDD();
        //$BDD->createDocLine();
    }

    public static function createDocumentF($params){
      //récupération des info envoyé depuis angular
      $contrats = (array)json_decode($params['contrats']);

      $contrat = $contrats[0];
      //Création de la boucle pour chaque contrat
      foreach($contrats as $contrat){
        //Initialisation des données supplémentaire
        //Calcule de la date précédente
        switch($contrat->periodicite){
          case "Annuelle":
            $month = 12;
            $contrat->prixF = $contrat->prix;
            break;
          case "Semestrielle":
            $month = 6;
            $contrat->prixF = $contrat->prix / 2;
            break;
          case "Trimestrielle":
            $month = 3;
            $contrat->prixF = $contrat->prix / 4;
            break;
          case "Mensuelle":
            $month = 1;
            $contrat->prixF = $contrat->prix / 12;
            break;
        }

        //periodicite masculin
        $typeM = substr($contrat->periodicite,0,strlen($contrat->periodicite)-2);

        $date = DateTime::createFromFormat('d/m/Y',$contrat->date_prochaine_facture);
        $dateCopy = clone $date;
        $contrat->date_debut_periode = $date->sub(new DateInterval('P'.$month.'M'))->format('d/m/Y');
        $contrat->date_fin_periode = $dateCopy->sub(new DateInterval('P1D'))->format('d/m/Y');

        $date1 = DateTime::createFromFormat('d/m/Y',$contrat->date_debut_periode);
        $date2 = DateTime::createFromFormat('d/m/Y',$contrat->date_debut);
        $monthDiff = $date1->diff($date2)->format('%m');

        switch($contrat->periodicite){
          case "Annuelle":
            $contrat->designation = "Maintenance";
            break;
          case "Semestrielle":
            $contrat->designation = "Maintenance Sem " . (($monthDiff*2)/12+1);
            break;
          case "Trimestrielle":
            $contrat->designation = "Maintenance Tri " . (($monthDiff*4)/12+1);
            break;
          case "Mensuelle":
            $contrat->designation = "Maint Mens " . (($monthDiff*12)/12+1);
            break;
        }
      //}
        //var_dump($contrats);
        //die();
        //Pour chaque ligne
        $BDD = self::getBDD();

        //Récupération du lieu de livraison
        $LI_NO = $BDD->getLi_NO($contrat->org);
        $LI_NO = odbc_fetch_array($LI_NO)['LI_No'];

        //Création de l'entete
        do{
            $DO_PIECE = self::getDoPieceFA4($BDD);
            $coord03 = DateTime::createFromFormat('d/m/Y',$contrat->date_debut_periode)->format('d/m/y') . '-' . DateTime::createFromFormat('d/m/Y',$contrat->date_fin_periode)->format('d/m/y');
            $ENTETE = $BDD->createDocEnteteContrat($contrat->org,2,$LI_NO,$DO_PIECE,$contrat->designation,$coord03);
        } while ($ENTETE === false);

        //Ajout de la ligne renouvellement
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,1000,'Renouvellement du contrat de maintenance');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,2000,'');

        //Ajout de la ligne du contrat
        //Switch calcule du prix : mensuelle = /12;trimestielle = /4; semestrielle/2;
        $info = $BDD->getArticle($contrat->type);
        $BDD->createDocLineContrat($contrat->org,$DO_PIECE,3000,$contrat->type,1,0,odbc_fetch_array($info)['AR_DESIGN'],$contrat->prixF);


        //Ajout des ligne de facturation
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,4000,'');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,5000,'Facture ' . $contrat->periodicite);
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,6000,'du ' . $contrat->date_debut_periode . ' au ' . $contrat->date_fin_periode);

        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,7000,'');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,8000,'');

        //Switch masculin = $typeM

        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,9000,'Contrat ' . $typeM . ' de ' . $contrat->prix  . ' EUR HT');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,10000,'du ' . $contrat->date_debut . ' au ' . $contrat->date_fin);

        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,11000,'');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,12000,'');

        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,13000,'Nos factures de maintenance sont payables à réception.');
        $BDD->createDocLineDesign($contrat->org,$DO_PIECE,14000,'D\'avance, nous vous en remercions');

        //$BDD = self::getBDD();
        //$BDD->createDocLine();

        //sleep(5);

        }
    }

    private static function getDoPiece($BDD){
        //Récupération du nouveau BL
        $DO_PIECE = $BDD->getNewNumBL();
        $DO_PIECE = odbc_fetch_array($DO_PIECE)['DC_Piece'];
        return $DO_PIECE;
    }

    private static function getDoPieceFA4($BDD){
        //Récupération du nouveau numero de facture
        $DO_PIECE = $BDD->getNewNumFA4();
        $DO_PIECE = odbc_fetch_array($DO_PIECE)['DC_Piece'];
        return $DO_PIECE;
    }

}

?>
