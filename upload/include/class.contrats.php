<?php

class ContratModel extends VerySimpleModel {
    static $meta = array(
        'table' => 'ost_contrat',
        'pk' => array('id')
    );

    const PERM_CREATE =     'contrat.create';
    const PERM_EDIT   =     'contrat.edit';
    const PERM_VIEW   =     'contrat.view';

    static protected $perms = array(
        self::PERM_CREATE => array(
            'title' => /* @trans */ 'Create',
            'desc' => /* @trans */ 'Ability to add new contrats',
            'primary' => true,
        ),
        self::PERM_EDIT => array(
            'title' => /* @trans */ 'Edit',
            'desc' => /* @trans */ 'Ability to manage contrat information',
            'primary' => true,
        ),
        self::PERM_VIEW => array(
            'title' => /* @trans */ 'View',
            'desc' => /* @trans */ 'Ability to view contrat information',
            'primary' => true,
        ),
    );

    static function getPermissions() {
        return self::$perms;
    }

}

include_once INCLUDE_DIR.'class.role.php';
RolePermission::register(/* @trans */ 'Contrats', ContratModel::getPermissions());

class Contrat extends ContratModel
implements TemplateVariable {

    var $_entries;
    var $_forms;

    static function getVarScope() {
        return '';
    }

    static function fromVars($vars, $create=true, $update=false) {

        if ($create)
            $contrat = new Contrat();
        elseif ($update)
            $contrat = static::lookup(array('id'=>$vars['id']));


        $contrat->code = $vars['code'];
        $contrat->org = $vars['org'];
        $contrat->etat = $vars['etat'];
        $contrat->date_debut = $vars['date_debut'];
        $contrat->date_fin = $vars['date_fin'];
        $contrat->type = $vars['type'];
        $contrat->periodicite = $vars['periodicite'];
        $contrat->date_prochaine_facture = $vars['date_prochaine_facture'];
        $contrat->tva = $vars['tva'];
        $contrat->compte_compta = $vars['compte_compta'];
        $contrat->prix = $vars['prix'];
        $contrat->comments = $vars['comments'];

        try {
            $contrat->save(true);
        }
        catch (OrmException $e) {
            return null;
        }

        return $contrat;
    }
  }
