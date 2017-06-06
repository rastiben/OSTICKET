<?php

class StockModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_stock',
        'pk' => array('id'),
    );
}

class HistoriqueModel extends VerySimpleModel {
  static $meta = array(
      'table' => 'ost_stock_historique',
      'pk' => array('id'),
  );
}

class Stock extends ContratModel
implements TemplateVariable {

    var $_entries;
    var $_forms;

    static function getVarScope() {
        return '';
    }

    static function fromVars($vars, $create=true, $update=false) {

        if ($create)
            $stock = new Stock();
        elseif ($update)
            $stock = static::lookup(array('id'=>$vars['id']));


        $stock->designation = $vars['designation'];
        $stock->categorie_id = $vars['categorie_id'];
        $stock->marque = $vars['marque'];
        $stock->numserie = $vars['numserie'];
        $stock->dispo = $vars['dispo'];

        try {
            $stock->save(true);
        }
        catch (OrmException $e) {
            return null;
        }

        return $stock;
    }
  }
