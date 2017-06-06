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

  function get($id){
    $stock = StockModel::objects()->filter(array('id'=>$id));

    foreach ($stock as $key => $values) {
      $temp = $values->ht;
    }

    Http::response(200, json_encode($temp));
  }

  function add(){
    $vars = (array)json_decode(file_get_contents("php://input"));

    if (($stock = Stock::fromVars($vars)))
        Http::response(201, json_encode($stock->ht));
  }


  function indexHistoriques($id){
    $historiques = HistoriqueModel::objects()->filter(array("stock_id"=>$id));
    $temp = [];

    foreach ($historiques as $key => $historique) {

      array_push($temp,$historique->ht);

    }

    Http::response(200, json_encode($temp));
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
