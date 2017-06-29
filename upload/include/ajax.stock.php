<?php

require_once(INCLUDE_DIR.'class.stocks.php');

class StocksAjaxAPI extends AjaxController {

  function index(){
    $stocks = StockModel::objects();
    $temp = [];

    foreach ($stocks as $key => $stock) {

      array_push($temp,$stock->ht);

    }

    Http::response(200, json_encode($temp));
  }

  function view($id){
    $stock = StockModel::objects()->filter(array('id'=>$id));

    foreach ($stock as $key => $values) {
      $temp = $values->ht;
    }

    Http::response(200, json_encode($temp));
  }

  function add(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if(StockModel::objects()->filter(array('numserie'=>$vars['numserie']))->count() == 0){
      if(($stock = Stock::fromVars($vars)))
          Http::response(201, json_encode($stock->ht));
    } else {
      Http::response(400, "Ce numéro de série existe déjà");
    }
  }

  function addHistorique(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($historique = Historique::fromVars($vars)))
        Http::response(201, json_encode($historique->ht));
  }


  function indexHistoriques($id){
    $historiques = HistoriqueModel::objects()
    ->filter(array("stock_id"=>$id))
    ->order_by("date",QuerySet::DESC);

    $temp = [];

    foreach ($historiques as $key => $historique) {

      array_push($temp,$historique->ht);

    }

    Http::response(200, json_encode($temp));
  }

  function remove($id){
    if (!($stock = StockModel::lookup($id)))
        Http::response(404, 'Unknown stock');

    if ($stock->delete())
        Http::response(204, json_encode($stock->ht));
  }

  /*function add(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($contrat = Contrat::fromVars($vars)))
        Http::response(201, json_encode($contrat->ht));
  }

  function update(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($contrat = Contrat::fromVars($vars,false,true)))
        Http::response(201, json_encode($contrat->ht));
  }

  function delete($id){
    if (!($contrat = Contrat::lookup($id)))
        Http::response(404, 'Unknown organization');

    if ($contrat->delete())
        Http::response(204, json_encode($contrat->ht));
    //'Organization deleted successfully'
  }*/

}
