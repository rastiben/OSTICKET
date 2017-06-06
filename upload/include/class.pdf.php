<?php
/*********************************************************************
    class.pdf.php

    Ticket PDF Export

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

define('THIS_DIR', str_replace('\\', '/', Misc::realpath(dirname(__FILE__))) . '/'); //Include path..

require_once(INCLUDE_DIR.'mpdf/mpdf.php');

class mPDFWithLocalImages extends mPDF {
    function WriteHtml($html) {
        static $filenumber = 1;
        $args = func_get_args();
        $self = $this;
        $images = $cids = array();
        // Try and get information for all the files in one query
        if (preg_match_all('/"cid:([\w._-]{32})"/', $html, $cids)) {
            foreach (AttachmentFile::objects()
                ->filter(array('key__in' => $cids[1]))
                as $file
            ) {
                $images[strtolower($file->getKey())] = $file;
            }
        }
        $args[0] = preg_replace_callback('/"cid:([\w.-]{32})"/',
            function($match) use ($self, $images, &$filenumber) {
                if (!($file = @$images[strtolower($match[1])]))
                    return $match[0];
                $key = "__attached_file_".$filenumber++;
                $self->{$key} = $file->getData();
                return 'var:'.$key;
            },
            $html
        );
        return call_user_func_array(array('parent', 'WriteHtml'), $args);
    }
}

class Ticket2PDF extends mPDFWithLocalImages
{

	var $includenotes = false;

	var $pageOffset = 0;

    var $ticket = null;

	function __construct($ticket, $psize='Letter', $notes=false, $document, $id=null) {

        global $thisstaff;

        $this->ticket = $ticket;
        $this->includenotes = $notes;

        parent::__construct('', $psize);

        if($document == "rapport")
            $this->print_rapport();
        else if($document == "fs")
            $this->print_fiche_suivi($id);
        else if($document == "atelier")
            $this->print_atelier();
        else
            $this->_print();
	}

    function getTicket() {
        return $this->ticket;
    }

    function _print() {
        global $thisstaff, $thisclient, $cfg;

        if(!($ticket=$this->getTicket()))
            return;

        ob_start();
        if ($thisstaff)
            include STAFFINC_DIR.'templates/ticket-print.tmpl.php';
        elseif ($thisclient)
            include CLIENTINC_DIR.'templates/ticket-print.tmpl.php';
        else
            return;
        $html = ob_get_clean();

        $this->WriteHtml($html, 0, true, true);
    }

    function print_atelier(){
        require_once(SCP_DIR.'Request/Atelier.php');

        $atelier = Atelier::getInstance();
        $planches = $atelier->getPlanches();

        ob_start();
            include STAFFINC_DIR.'templates/atelier-print.tmpl.php';
        $html = ob_get_clean();

        //http://localhost:8080/osTicket/upload/assets/atelier/atelier.png

        $this->WriteHtml($html, 0, true, true);
    }

    function print_fiche_suivi($id) {
        global $thisstaff, $thisclient, $cfg;

        //if(!($ticket=$this->getTicket()))
            //return;

        ob_start();
            include STAFFINC_DIR.'templates/fs-print.tmpl.php';
        $html = ob_get_clean();

        $this->WriteHtml($html, 0, true, true);

        $html = '<table width="100%" style="margin-bottom:50px">
        <tbody>
            <tr>
                <td style="border-right:2px solid black;" width="50%" style="font-size:40px">
                <img height="100" style="float:right" src=' . (INCLUDE_DIR . 'fpdf/logo_OK.jpg').' class="logo"/><br>
                FICHE DE SUIVI</td>
                <td style="border-left:2px solid black;padding-left:15px;vertical-align:top" width="50%">'.$field->org.'<br>
                '.$field->contact->address.'<br>
                '.$field->contact->tel.'</td>
            </tr>
        </tbody>
        </table>


        <table width="100%" style="border:1px solid black;border-collapse: collapse;margin-bottom:50px">
           <thead>
              <tr>
               <th style="background:gray;border:1px solid black;" width="34%">Date d\'ouverture</th>
               <th style="background:gray;border:1px solid black;" width="34%">Technicien</th>
               <th style="background:gray;border:1px solid black;" width="34%">Type</th>
               </tr>
           </thead>
            <tbody>
                <tr>
                    <td style="text-align:center;border:1px solid black;">'. strftime("%d/%m/%Y", strtotime( $field->dateOuverture)) .'</td>
                    <td style="text-align:center;border:1px solid black;">'.$field->tech->name.'</td>
                    <td style="text-align:center;border:1px solid black;">'.$field->type.'</td>
                </tr>
            </tbody>
        </table>


        <h2 style="text-align:center">Identification du poste (VD)</h2>

        <table width="100%" style="border:1px solid black;border-collapse: collapse;margin-bottom:50px">
            <tbody>
                <tr>
                    <td width="50%" style="vertical-align:top;border:1px solid black;">
                        <p>'.$field->marque.'</p>
                        <p>'.$field->model.'</p>
                        <p>'.$field->sn.'</p>
                    </td>
                    <td width="50%" style="vertical-align:top;border:1px solid black;">
                        <p>'.$field->os.'</p>
                        <p>'.$field->motDePasse.' '.$field->login.'</p>
                        <p>'.$field->office.'</p>
                        <p>'.$field->autreSoft.'</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2 style="text-align:center">Description du problème</h2>

        <table width="100%" style="border:1px solid black;border-collapse: collapse;border-bottom:none">
            <tbody>
                <tr>
                    <td style="vertical-align:top;border:1px solid black;border-bottom:none">
                        '.$field->description.'
                    </td>
                </tr>
            </tbody>
        </table>';

        $this->WriteHtml($html, 0, true, true);

        $manquant = (100*$this->y)/$this->h;
        $html = "<div style='position:absolute;top:".$manquant."%;bottom:250px;left:37.8px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";

        $this->WriteHtml($html, 0, true, true);

        $manquant = (100*$this->y)/$this->h;
        $html = "<div style='position: absolute;top:805px;bottom: 210px;left:37.8px;right:37.8px;border:1px solid black;'>Accessoires : ".$field->accessoire."</div>";

        $this->WriteHtml($html, 0, true, true);

        $html = '<div class="signature"><h5>Cachet et signature du client le</h5>
        ' . ( empty($img) ? '' : '<img src="'. $img . '"></img>' ) . '
        </div>';

        $this->SetHTMLFooter($html);

    }

    function print_rapport(){
        global $thisstaff, $thisclient, $cfg;

        if(!($ticket=$this->getTicket()))
            return;

        $data = (array)json_decode(trim(file_get_contents('php://input')));
        $img = $data['img'];
        /*if(!empty($img)){
          $path = '../images/fpdf/';

          //Récupération des fichiers dans le dossier temporaire des signatures
          $all_files = scandir($path,1);
          $last_files = count($all_files) == 2 ? null : $all_files[0];

          //Création du nouveau nom de fichier.
          $filename = empty($last_files) ? '0.jpg' : intval(substr($last_files,0,strlen($last_files)-strpos($last_files,".")))+1 . ".jpg";
          $filepath = $path.$filename;

          // If a physical file is not available then create it
          // If the DB data is fresher than the file then make a new file
          if(!is_file($filepath) || strtotime($row['last_update']) > filemtime($filepath))
          {
              $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $img));
              $result = file_put_contents($filepath, $data);

              if($result === FALSE)
              {
                  die(__FILE__.'<br>Error - Line #'.__LINE__.': Could not create '.$filepath);
              }
          }
        }*/
        //die();

        ob_start();
            include STAFFINC_DIR.'templates/rapport-print.tmpl.php';
        $html = ob_get_clean();

        $this->WriteHtml($html, 0, true, true);

        //Suppression de l'image
        //unlink($filepath);

        $horaires = Rapport::getInstance()->getRapportsHoraires($_GET['idR']);

        foreach($horaires as $key=>$horaire){

        $arriveInter = new DateTime($horaire['arrive_inter']);
        $departInter = new DateTime($horaire['depart_inter']);
        $comment = "";
        //Découpage en nouvelle ligne

        $content = $horaire['comment'];
        $newcontent = preg_replace("/<p[^>]*?>/", "", $content);
        $newcontent = str_replace("</p>", "<br>", $newcontent);
        $array = preg_split('/<br[^>]*>/i',$newcontent);
        //$this->WriteHtml(count($array), 0, true, true);
        $newarray = [];
        foreach($array as $line){
            $temp = [];
            if (strlen($line) >= 70){
                //$this->WriteHtml("titi", 0, true, true);
                $temptext = wordwrap($line, 70, "<br>");
                $temp = preg_split('/<br[^>]*>/i',$temptext);
                foreach($temp as $templine){
                    array_push($newarray,$templine);
                }
            } else {
                array_push($newarray,$line);
            }
        }


        //$this->WriteHtml(print_r($newarray,true), 0, true, true);
        $premierPassage = true;

        $cpt=0;
        foreach($newarray as $nb=>$line){
            $cpt += 1;

            $comment .= '<tr>
                    <td style="border:1px solid black;border-top:none;border-bottom:none" >'.$line.'</td>
                    <td style="border:1px solid black;border-top:none;border-bottom:none"></td>
                    <td style="border:1px solid black;border-top:none;border-bottom:none"></td>
                    <td style="border-left:1px solid black"></td>
                </tr>';
            if(($cpt+1)%45 == 0 || ($premierPassage && ($cpt+1)%28 == 0)){
                $cpt = 0;
                $html = '<table autosize="1" style="page-break-inside: auto;border-collapse: collapse;border-spacing: 0;border-right:1px solid black;margin-top:15px;overflow:wrap" width="100%">
                   <thead>
                    <tr>
                        <th width="70%" style="border:1px solid black;" >Libellé article et commentaires</th>
                        <th width="10%" style="border:1px solid black;">quantité</th>
                        <th width="10%" style="border:1px solid black;">P.U.</th>
                        <th width="10%" style="border-bottom:1px solid black;border-left:1px solid black;border-top:1px solid black">Prix total</th>
                    </tr>
                   </thead>
                   <tbody>
                   <tr>
                        <td style="border-left:1px solid black;border-right:1px solid black">';
                if($premierPassage){
                    $html .= '<b>Note d\'intervention du '. $arriveInter->format('d/m/Y') .'<br>
                        De : '. $arriveInter->format('H:i') . ' à ' . $departInter->format('H:i') .'
                        <br><br>
                        Commentaires : <br><br></b>';
                }

                $html .= '</td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                   </tr>
                   '.$comment.'
                   </tbody>
                </table>';
                $this->WriteHtml($html, 0, true, true);

                $manquant = (100*$this->y)/$this->h;

                if($this->y > 230){
                    $html = "<div style='position:absolute;top:".$manquant."%;bottom:40px;left:37.8px;right:259.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:40px;left:555.4px;right:185.7px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:40px;left:629.4px;right:111.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:40px;left:703.3px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";
                    $this->WriteHtml($html, 0, true, true);
                    if($premierPassage == false) $this->AddPage();
                } else {
                    if(count($newarray) == 1){
                        $html = "<div style='position:absolute;top:".$manquant."%;bottom:210px;left:37.8px;right:259.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:210px;left:555.4px;right:185.7px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:210px;left:629.4px;right:111.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:210px;left:703.3px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";
                    } else {
                        $html = "<div style='position:absolute;top:".$manquant."%;bottom:40px;left:37.8px;right:259.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:40px;left:555.4px;right:185.7px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:40px;left:629.4px;right:111.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                        <div style='position:absolute;top:".$manquant."%;bottom:40px;left:703.3px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";
                    }
                    $this->WriteHtml($html, 0, true, true);

                }

                $premierPassage = false;
                $comment = "";
            }
            //$this->WriteHtml($html, 0, true, true);
        }
        if($cpt != 0 && ($cpt+1) < 45 || ($premierPassage && ($cpt+1) < 28)){
            $html = '<table autosize="1" style="page-break-inside: auto;border-collapse: collapse;border-spacing: 0;border-right:1px solid black;margin-top:15px;overflow:wrap" width="100%">
                   <thead>
                    <tr>
                        <th width="70%" style="border:1px solid black;" >Libellé article et commentaires</th>
                        <th width="10%" style="border:1px solid black;">quantité</th>
                        <th width="10%" style="border:1px solid black;">P.U.</th>
                        <th width="10%" style="border-bottom:1px solid black;border-left:1px solid black;border-top:1px solid black">Prix total</th>
                    </tr>
                   </thead>
                   <tbody>
                   <tr>
                        <td style="border-left:1px solid black;border-right:1px solid black">';
                if($premierPassage){
                    $html .= '<b>Note d\'intervention du '. $arriveInter->format('d/m/Y') .'<br>
                        De : '. $arriveInter->format('H:i') . ' à ' . $departInter->format('H:i') .'
                        <br><br>
                        Commentaires : <br><br></b>';
                }

                $html .= '</td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                        <td style="border-left:1px solid black;border-right:1px solid black"></td>
                   </tr>
                   '.$comment.'
                   </tbody>
                </table>';
            //$html .= $key." ".(count($horaires)-1)." ".count($array);
            $this->WriteHtml($html, 0, true, true);
            if($key == (count($horaires)-1)){
                $manquant = (100*$this->y)/$this->h;
                if(count($array) <= 25){
                    $html = "<div style='position:absolute;top:".$manquant."%;bottom:210px;left:37.8px;right:259.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:210px;left:555.4px;right:185.7px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:210px;left:629.4px;right:111.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                    <div style='position:absolute;top:".$manquant."%;bottom:210px;left:703.3px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";
                    $this->WriteHtml($html, 0, true, true);
                }

            } else if($key < count($horaires)-1) {

                $manquant = (100*$this->y)/$this->h;
                $html = "<div style='position:absolute;top:".$manquant."%;bottom:40px;left:37.8px;right:259.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                <div style='position:absolute;top:".$manquant."%;bottom:40px;left:555.4px;right:185.7px;border:1px solid black;border-top:none;'>&nbsp;</div>
                <div style='position:absolute;top:".$manquant."%;bottom:40px;left:629.4px;right:111.6px;border:1px solid black;border-top:none;'>&nbsp;</div>
                <div style='position:absolute;top:".$manquant."%;bottom:40px;left:703.3px;right:37.8px;border:1px solid black;border-top:none;'>&nbsp;</div>";
                $this->WriteHtml($html, 0, true, true);

                $this->AddPage();

            } else {

                $manquant = (100*$this->y)/$this->h;
                $borderTop = "";
                if($this->y > 230){
                    $borderTop = "border-top:1px solid black;";
                    $this->AddPage();
                } else {
                    $borderTop = "border-top:none";
                }

                $html = "<div style='position:absolute;top:37.8px;bottom:210px;left:37.8px;right:259.6px;border:1px solid black;".$borderTop."'>&nbsp;</div>
                <div style='position:absolute;top:37.8px;bottom:210px;left:555.4px;right:185.7px;border:1px solid black;".$borderTop."'>&nbsp;</div>
                <div style='position:absolute;top:37.8px;bottom:210px;left:629.4px;right:111.6px;border:1px solid black;".$borderTop."'>&nbsp;</div>
                <div style='position:absolute;top:37.8px;bottom:210px;left:703.3px;right:37.8px;border:1px solid black;".$borderTop."'>&nbsp;</div>";
                $this->WriteHtml($html, 0, true, true);

            }
        }
        //$this->WriteHtml("<p>".$this->h." ".$this->y." ".$height."</p>", 0, true, true);
    }

        //echo $html;
        //die();
        //$this->WriteHtml($this->page, 0, true, true);
        //$this->SetHTMLFooter('<img src="'. $img . '"></img>');
    }
}


// Task print
class Task2PDF extends mPDFWithLocalImages {

    var $options = array();
    var $task = null;

    function __construct($task, $options=array()) {

        $this->task = $task;
        $this->options = $options;

        parent::__construct('', $this->options['psize']);
        $this->_print();
    }

    function _print() {
        global $thisstaff, $cfg;

        if (!($task=$this->task) || !$thisstaff)
            return;

        ob_start();
        include STAFFINC_DIR.'templates/task-print.tmpl.php';
        $html = ob_get_clean();
        $this->WriteHtml($html, 0, true, true);

    }
}

?>
