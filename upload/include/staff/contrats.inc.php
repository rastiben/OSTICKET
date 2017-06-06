<?php
  $permissions = $thisstaff->getPermission()->perms;
?>

<script src="http://cdn.jsdelivr.net/alasql/0.3/alasql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.10.3/xlsx.full.min.js"></script>

<div ng-controller="contratCtrl" ng-init="permissions = <?= htmlspecialchars(json_encode($permissions)) ?>">

  <modal class="contratModals" lolo="modal1" data-permissions="permissions" data-header="{{header}}" data-contrat="contrat" data-valid="{{valid}}" data-call-remove="remove(contrat)" data-ng-click-left-button="cancel()" data-ng-click-right-button="save(contrat)"></modal>
  <a ng-if="permissions | havePerms:'contrat.create'" href="#{{modal1}}" role="button" class="btn btn-success newContrat" ng-click="modalInfo('Ajout d\'un contrat','Créer le contrat',null,'create')" data-toggle="modal">Nouveau contrat</a>
  <a ng-if="permissions | havePerms:'contrat.edit'" role="button" class="btn btn-primary" ng-click="facturer()">Facturer</a>

  <div class="filters checkbox">
    <label><input type="checkbox" ng-model="etatFilter" ng-true-value="'Actif'" ng-false-value="''" ng-init="etatFilter='Actif'">Ne pas afficher les contrats soldés</label>
  </div>
  <div class="block" style="float: left;width: 100%;">
    <div layout="row" ng-show="invoiceLoading" class="invoiceLoading" layout-sm="column" layout-align="space-around">
      <md-progress-circular md-mode="indeterminate"></md-progress-circular>
    </div>
    <table class="table table-striped contratTable">
      <thead>
        <th>Code<span class="sortorder" ng-click="sortBy('code')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='code' ,'glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'code'}"></span>
          <input ng-model="codeFilter" />
        </th>
        <th>Organisation<span class="sortorder" ng-click="sortBy('org')"  ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='org','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'org'}"></span>
          <input ng-model="orgFilter" />
        </th>
        <th ng-if="permissions | havePerms:'contrat.edit'" width="100">Periodicité<span class="sortorder" ng-click="sortBy('periodicite')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='periodicite','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'periodicite'}"></span>
          <input ng-model="periodiciteFilter" />
        </th>
        <th width="150">Date de debut<span class="sortorder" ng-click="sortBy('date_debut')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='date_debut','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'date_debut'}"></span>
          <input ng-model="date_debutFilter" />
        </th>
        <th width="150">Date de fin<span class="sortorder" ng-click="sortBy('date_fin')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='date_fin','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'date_fin'}"></span>
          <input ng-model="date_finFilter" />
        </th>
        <th ng-if="permissions | havePerms:'contrat.edit'" width="150">Prix<span class="sortorder" ng-click="sortBy('prix')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='prix','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'prix'}"></span>
          <input ng-model="prixFilter" />
        </th>
        <th>Type<span class="sortorder" ng-click="sortBy('type')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='type','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'type'}"></span>
          <input ng-model="typeFilter" />
        </th>
        <th width="150">Prochaine date<span class="sortorder" ng-click="sortBy('date_prochaine_facture')" ng-class="{'glyphicon glyphicon-sort-by-alphabet-alt':reverse && propertyName=='date_prochaine_facture','glyphicon glyphicon-sort-by-alphabet':!reverse || propertyName != 'date_prochaine_facture'}"></span>
          <input ng-model="createdFilter" />
        </th>
      </thead>
      <tbody>
        <tr href="#{{modal1}}" ng-click="modalInfo('Modification du contrat : ','Modifier le contrat',contrat,'update')" data-toggle="modal" ng-repeat="contrat in (filteredContrats = (contrats | orderBy:propertyName:reverse | filter:{etat:etatFilter, code: codeFilter, org:orgFilter, periodicite:periodiciteFilter, date_debut:date_debutFilter, date_fin:date_finFilter, prix:prixFilter, type:typeFilter, date_prochaine_facture:createdFilter}))">
          <td><span ng-show="contrat.comments" class="glyphicon glyphicon-info-sign"></span>{{contrat.code}}</td>
          <td>{{contrat.org}}</td>
          <td ng-if="permissions | havePerms:'contrat.edit'">{{contrat.periodicite | limitTo:1}}</td>
          <td>{{contrat.date_debut}}</td>
          <td>{{contrat.date_fin}}</td>
          <td ng-if="permissions | havePerms:'contrat.edit'" class="text-right">{{contrat.prix | currency:'€':2}}</td>
          <td>{{contrat.type}}</td>
          <td>{{contrat.date_prochaine_facture}}</td>
        </tr>
      </tbody>
      <tfooter>
        <tr>
          <td><hr /></td>
          <td><hr /></td>
          <td><hr /></td>
          <td><hr /></td>
          <td><hr /></td>
          <td ng-if="permissions | havePerms:'contrat.edit'" class="text-right">{{calcPrice() | currency:'€':2}}</td>
          <td><hr /></td>
          <td><hr /></td>
        </tr>
      </tfooter>
    </table>
  </div>
  <a class="btn btn-success invoicesBtn" ng-click="invoice()" ng-show="doInvoice">Valider</a>
  <a class="btn btn-danger invoicesBtn" ng-click="doInvoice=false;createdFilter=''" ng-show="doInvoice">Annuler</a>
  <a class="btn btn-primary invoicesBtn" ng-click="printList()" ng-show="doInvoice"><span class="glyphicon glyphicon-print"></span></a>
</div>

<script src="../js/angular-resource.min.js"></script>
<script src="./js/moment.js"></script>
<script src="./js/contrats.js"></script>

<script>

$(document).on('click', 'a.popup-dialog', function(e) {
    e.preventDefault();
    $.contratLookup('ajax.php/' + $(this).attr('href').substr(1), function (contrat) {
        var url = window.location.href;
        if (contrat && contrat.id)
            url = 'contrat.php?id='+contrat.id;
        $.pjax({url: url, container: '#pjax-container'})
        return false;
     });

    return false;
});

</script>
