<?php

class StockModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_stock',
        'pk' => array('id')
    );

    public function setThreadEntry($thread_entry_id){
      $this->thread_entry_id = $thread_entry_id;
      $this->save(true);
    }

    public function setDispo($dispo){
      $this->dispo = $dispo;
      $this->save(true);
    }
}

class HistoriqueModel extends VerySimpleModel {
  static $meta = array(
      'table' => 'ost_stock_historique',
      'pk' => array('id'),
  );

  public function setThreadEntry($thread_entry_id){
    $this->thread_entry_id = $thread_entry_id;
    $this->save(true);
  }
}

class Stock extends StockModel
implements TemplateVariable {

    var $_entries;
    var $_forms;

    static function getVarScope() {
        return '';
    }

    static function fromVars($vars, $create=true, $update=false) {

        if(isset($vars['id'])){
          $stock = static::lookup(array('id'=>$vars['id']));
        }
        else
          $stock = new Stock();

        $stock->designation = $vars['designation'];
        $stock->categorie = $vars['categorie'];
        $stock->marque = $vars['marque'];
        $stock->numserie = $vars['numserie'];
        $stock->dispo = $vars['dispo'];
        $stock->thread_entry_id = $vars['thread_entry_id'];

        try {
            $stock->save(true);
        }
        catch (OrmException $e) {
            return null;
        }

        return $stock;
    }

  }

  class Historique extends HistoriqueModel
  implements TemplateVariable {

      var $_entries;
      var $_forms;

      static function getVarScope() {
          return '';
      }

      static function fromVars($vars, $create=true, $update=false) {

          if ($create)
              $historique = new Historique();
          elseif ($update)
              $historique = static::lookup(array('id'=>$vars['id']));

          foreach($vars as $key=>$value){
            $historique->$key = $value;
          }

          try {
              $historique->save(true);
          }
          catch (OrmException $e) {
              return null;
          }

          return $historique;
      }
    }
