<?php
/*********************************************************************
    orgs.php

    Peter Rotich <peter@osticket.com>
    Jared Hancock <jared@osticket.com>
    Copyright (c)  2006-2014 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

require_once INCLUDE_DIR . 'class.org.php';
require_once INCLUDE_DIR . 'class.organization.php';
require_once INCLUDE_DIR . 'class.note.php';

$org = null;
if ($_REQUEST['org_id'])
    $org = Organization::lookup($_REQUEST['org_id']);


    if(array_key_exists('stats',$_REQUEST)){

      $org = new Organisation(["411VDOC","150 RUE DES HAUTS DE LA CHAUME","","86280","SAINT BENOIT","",""]);

      $typeInter = [];
      $query = 'SELECT helpTopic.topic FROM ost_help_topic helpTopic';
      $result = db_query($query);
      while ($row = db_fetch_array($result)) {
        array_push($typeInter,$row['topic']);
      }

      $query = 'SELECT rapport.id,rapport.contrat,rapport.instal,helpTopic.couleur,helpTopic.topic FROM ost_rapport rapport '
            . 'INNER JOIN ost_ticket ticket ON (rapport.id_ticket = ticket.ticket_id) '
            . 'INNER JOIN ost_help_topic helpTopic ON (rapport.topic_id = helpTopic.topic_id)'
            . 'INNER JOIN ost_user user ON (ticket.user_id = user.id AND user.org_name = \''.$org->getName().'\')'
            . 'WHERE rapport.date_inter >= \''.$_GET["datedebut"].'\' AND rapport.date_inter <= \''.$_GET["datefin"].'\'';

      switch($_REQUEST['stats']){
        case "contrat":
          $query .= 'AND rapport.contrat != \'0\' AND rapport.instal = \'0\'';
          break;
        case "instal":
          $query .= 'AND rapport.contrat = \'0\' AND rapport.instal != \'0\'';
          break;
        case "formation":
          $query .= 'AND rapport.contrat = \'0\' AND rapport.instal = \'0\'';
          break;
      }

      if($_REQUEST['stats'] == "formation")
        $formation = true;

      $result = db_query($query);
      $contrats = [];
      $typeContrats = [];
      $dataSets = [];

      while ($row = db_fetch_array($result)) {

        if(!empty($row[$_REQUEST['stats']])
        && $row[$_REQUEST['stats']] != '0'
        && !in_array(array('name'=>$row[$_REQUEST['stats']],'couleur'=>$row['couleur'],'topic'=>$row['topic']),$typeContrats)){
          array_push($typeContrats,array('name'=>$row[$_REQUEST['stats']],'couleur'=>$row['couleur'],'topic'=>$row['topic']));
        } else if($formation && empty($typeContrats)){
          array_push($typeContrats,array('name'=>'Formation','couleur'=>$row['couleur']));
        }
        array_push($contrats,array('id'=>$row['id'],'contrat'=>$row['contrat'],'instal'=>$row['instal'],'topic'=>$row['topic']));

      }

      $horaires = [];
      $totalHoraires = 0;

      foreach ($typeContrats as $key => $type) {

          $horaires = [];

          if(!$formation){
            $filteredContrats = array_filter($contrats, function($elem) use($type){
                return $elem[$_REQUEST['stats']] == $type['name'] && $elem['topic'] == $type['topic'];
            });
          }

          $toFilter = $formation ? $contrats : $filteredContrats;


          foreach ($toFilter as $contrat) {
              $query = 'SELECT horaire.arrive_inter,horaire.depart_inter FROM ost_rapport_horaires horaire
              WHERE horaire.id_rapport = \''.$contrat['id'].'\'';
              $result = db_query($query);
              while ($row = db_fetch_array($result)) {
                array_push($horaires,(object)array('arrive_inter'=>$row['arrive_inter'],'depart_inter'=>$row['depart_inter']));
              }
          }

          //Calcule du temps passé


          foreach ($horaires as $horaire) {
            $arrive_inter = DateTime::createFromFormat('Y-m-d H:i:s',$horaire->arrive_inter);
            $depart_inter = DateTime::createFromFormat('Y-m-d H:i:s',$horaire->depart_inter);
            //Hour difference
            //var_dump($depart_inter->format('H:i:s') . ' - ' . $arrive_inter->format('H:i:s') . ' = ' . ($depart_inter->getTimestamp() - $arrive_inter->getTimestamp()));
            $totalHours += $depart_inter->getTimestamp() - $arrive_inter->getTimestamp();
          }


          $sets = count(array_filter($dataSets, function($elem) use($type){
              return $elem['label'] == $type['name'];
          }));

          if($sets == 0){
            $dataSets[$type['name']]['label'] = $type['name'];
            $dataSets[$type['name']]['data'] = [];
            for ($i=0; $i < count($typeInter); $i++) {
              array_push($dataSets[$type['name']]['data'],0);
            }
          }

          //var_dump($totalHours);
          //array_push($dataSets[$type['name']]['data'],$totalHours);
          $dataSets[$type['name']]['data'][array_search($type['topic'],$typeInter)] = $totalHours;
          $totalHoraires += $totalHours;


      }

      $result = [
        'labels'=>$typeInter,
        'data'=>array_values($dataSets)
      ];

      echo json_encode($result);
      die();

    }

if ($_POST) {
    switch ($_REQUEST['a']) {
    case 'import-users':
        if (!$org) {
            $errors['err'] = __('Organization ID must be specified for import');
            break;
        }
        $status = User::importFromPost($_FILES['import'] ?: $_POST['pasted'],
            array('org_id'=>$org->getId()));
        if (is_numeric($status))
            $msg = sprintf(__('Successfully imported %1$d %2$s'), $status,
                _N('end user', 'end users', $status));
        else
            $errors['err'] = $status;
        break;
    case 'remove-users':
        if (!$org)
            $errors['err'] = __('Trying to remove end users from an unknown organization');
        elseif (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('You must select at least %s.'),
                __('one end user'));
        } else {
            $i = 0;
            foreach ($_POST['ids'] as $k=>$v) {
                if (($u=User::lookup($v)) && $org->removeUser($u))
                    $i++;
            }
            $num = count($_POST['ids']);
            if ($i && $i == $num)
                $msg = sprintf(__('Successfully removed %s.'),
                    _N('selected end user', 'selected end users', $count));
            elseif ($i > 0)
                $warn = sprintf(__('%1$d of %2$d %3$s removed'), $i, $count,
                    _N('selected end user', 'selected end users', $count));
            elseif (!$errors['err'])
                $errors['err'] = sprintf(__('Unable to remove %s'),
                    _N('selected end user', 'selected end users', $count));
        }
        break;

    case 'mass_process':
        if (!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
            $errors['err'] = sprintf(__('You must select at least %s.'),
                __('one organization'));
        }
        else {
            $orgs = Organization::objects()->filter(
                array('id__in' => $_POST['ids'])
            );
            $count = 0;
            switch (strtolower($_POST['do'])) {
            case 'delete':
                foreach ($orgs as $O)
                    if ($O->delete())
                        $count++;
                break;

            default:
                $errors['err']=__('Unknown action - get technical help.');
            }
            if (!$errors['err'] && !$count) {
                $errors['err'] = __('Unable to manage any of the selected organizations');
            }
            elseif ($_POST['count'] && $count != $_POST['count']) {
                $warn = __('Not all selected items were updated');
            }
            elseif ($count) {
                $msg = __('Successfully managed selected organizations');
            }
        }
        break;

    default:
        $errors['err'] = __('Unknown action');
    }
} elseif (!$org && $_REQUEST['a'] == 'export') {
    require_once(INCLUDE_DIR.'class.export.php');
    $ts = strftime('%Y%m%d');
    if (!($query=$_SESSION[':Q:orgs']))
        $errors['err'] = __('Query token not found');
    elseif (!Export::saveOrganizations($query, __('organizations')."-$ts.csv", 'csv'))
        $errors['err'] = __('Internal error: Unable to export results');
}

/*TEST*/
$page = 'orgs.inc.php';
if ($_REQUEST['id']) {
    $page = 'org-view.inc.php';
    /*switch (strtolower($_REQUEST['t'])) {
    case 'tickets':
        if (isset($_SERVER['HTTP_X_PJAX'])) {
            $page='templates/tickets.tmpl.php';
            $pjax_container = @$_SERVER['HTTP_X_PJAX_CONTAINER'];
            require(STAFFINC_DIR.$page);
            return;
        } elseif ($_REQUEST['a'] == 'export' && ($query=$_SESSION[':O:tickets'])) {
            $filename = sprintf('%s-tickets-%s.csv',
                    $org->getName(), strftime('%Y%m%d'));
            if (!Export::saveTickets($query, $filename, 'csv'))
                $errors['err'] = __('Internal error: Unable to dump query results');
        }
        break;
    }*/
}

$nav->setTabActive('users');
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>
