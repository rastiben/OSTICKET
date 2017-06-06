<?php

//LIKE

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.rapport.php');

//Récupération des rapports.
$rapports = RapportModel::objects();

//Apply filter
if($_POST['org']){
    $rapports->filter(array('ticket__user__org_name__like'=>'%'.$_POST['org'].'%'));
}
if ($_POST['auteur']) {
    $rapports->filter(Q::any(array('staff__firstname__like'=>'%'.$_POST['auteur'].'%','staff__lastname__like'=>'%'.$_POST['auteur'].'%')));
}
if($_POST['type']){
    $rapports->filter(array('topic__topic__like'=>'%'.$_POST['type'].'%'));
}

$rapports->order_by('date_rapport','DESC');

$rapports->values('id','id_ticket','id_agent','date_rapport','date_inter','topic_id','contrat','instal','staff__firstname','staff__lastname','topic__topic','topic__couleur','ticket__user__org_name');

//Création de l'url
$args = array();
parse_str($_SERVER['QUERY_STRING'], $args);
unset($args['p']);
unset($args['_pjax']);

//Création de la pagination
$page=($_POST['p'] && is_numeric($_POST['p']))?$_POST['p']:1;
$count = $rapports->count();
$pageNav = new Pagenate($count, $page, '25');
$pageNav->setURL('rapports.php', (empty($args) ? '' : $args));
$rapports = $pageNav->paginate($rapports);

$rapportl = [];

foreach($rapports as $rapport){

    //echo $rapports->query->sql;
    //die();

    //Récupération des horaires
    $horaires = RapportHorairesModel::objects();
    $horaires->filter(array('id_rapport'=>$rapport['id']));
    $horaires->values('arrive_inter','depart_inter','comment');

    $rapport['horaires'] = [];

    foreach($horaires as $horaire){

        array_push($rapport['horaires'],$horaire);
    }

    //Récupération des stock
    $stocks = RapportStockModel::objects();
    $stocks->filter(array('id_rapport'=>$rapport['id']));
    $stocks->values('reference','quantite','prix');

    $rapport['stock'] = [];

    foreach($stocks as $stock){
        array_push($rapport['stock'],$stock);
    }


    array_push($rapportl,$rapport);

    //var_dump($rapport);

}

/*var_dump($labelsTotal);
var_dump($dataTotal);
var_dump($colorTotal);
die();*/



if(!empty($_POST)){
    echo json_encode(['rapports'=>$rapportl,
                      'pagination'=> sprintf('<ul class="pagination">%s</ul>',
                    $pageNav->getBSPageLinks(false,false))]);
} else {
    require_once(STAFFINC_DIR.'header.inc.php');
    require_once(STAFFINC_DIR.'rapports.inc.php');
    require_once(STAFFINC_DIR.'footer.inc.php');
}
