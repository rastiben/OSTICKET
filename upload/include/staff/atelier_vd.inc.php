<?php
  $permissions = $thisstaff->getPermission()->perms;
?>

<script src="http://cdn.jsdelivr.net/alasql/0.3/alasql.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.10.3/xlsx.full.min.js"></script>

<div ng-controller="vdController">

  <div class="block" style="float: left;width: 100%;">
    <div layout="row" ng-show="invoiceLoading" class="invoiceLoading" layout-sm="column" layout-align="space-around">
      <md-progress-circular md-mode="indeterminate"></md-progress-circular>
    </div>
    <table class="table table-striped contratTable">
      <thead>
        <th>VD<input ng-model="VDFilter" />
        </th>
        <th>Client<input ng-model="orgFilter" />
        </th>
        <th>Type<input ng-model="typeFilter" />
        </th>
        <th>Numero de serie<<input ng-model="numSerieFilter" />
        </th>
        <th>Version de Windows<input ng-model="versionWFilter" />
        </th>
        <th>Licence Windows<input ng-model="licenceWFilter" />
        </th>
        <th>Version Office<input ng-model="versionOFilter" />
        </th>
        <th>Licence Office<input ng-model="licenceOFilter" />
        </th>
        <th>Garantie<input ng-model="garantieFilter" />
        </th>
        <th>Debut Garantie<input ng-model="debutGarantieFilter" />
        </th>
        <th>Mail<input ng-model="mailFilter" />
        </th>
        <th>MDP<input ng-model="mdpFilter" />
        </th>
      </thead>
      <tbody>
        <!--<tr ">-->
        <tr ng-repeat="VD in filterVD = (VDS | filter:{VD: VDFilter,client:orgFilter,type:typeFilter,numeroSerie:numSerieFilter,versionWindows:versionWFilter,numLicenceW:licenceWFilter,versionOffice:versionOFilter,numLicenceO:licenceOFilter,garantie:garantieFilter,debutGarantie:debutGarantieFilter,mail:mailFilter,mdp:mdpFilter}) | pagination: pagination.currentPage : pagination.numPerPage">
          <td>{{VD.VD ? VD.VD : "VD"+VD.id}}</td>
          <td>{{VD.client}}</td>
          <td>{{VD.type}}</td>
          <td>{{VD.numeroSerie}}</td>
          <td>{{VD.versionWindows}}</td>
          <td>{{VD.numLicenceW}}</td>
          <td>{{VD.versionOffice}}</td>
          <td>{{VD.numLicenceO}}</td>
          <td>{{VD.garantie}}</td>
          <td>{{VD.debutGarantie}}</td>
          <td>{{VD.mail}}</td>
          <td>{{VD.mdp}}</td>
        </tr>
      </tbody>
    </table>
    <div class="text-center" ng-if="VDS">
      <ul uib-pagination total-items="filterVD.length" ng-model="pagination.currentPage" previous-text="&lsaquo;" max-size="pagination.maxSize" next-text="&rsaquo;" items-per-page="pagination.numPerPage"></uib-pagination>
    </div>
  </div>
</div>

<!--<script src="./js/docxtemplater.js"></script>
<script src="./js/jszip.js"></script>
<script src="./js/file-saver.min.js"></script>
<script src="./js/jszip-utils.js"></script>-->

<script src="../js/angular-resource.min.js"></script>
<script src="../js/ui-bootstrap.min.js"></script>
<!--<script src="./js/moment.js"></script>-->

<script>
  angular.module('myApp').requires.push('ngResource');
  angular.module('myApp').requires.push('ui.bootstrap');

  app.factory("vdFactory",["$resource",function($resource){
    return $resource("ajax.php/vd",null);
  }]);

  app.controller("vdController",["$scope","vdFactory",function($scope,vdFactory){

    $scope.VDS = vdFactory.query();

    $scope.pagination = {
      currentPage: 1,
      maxSize: 5,
      numPerPage: 25
    };

  }]);

  app.filter('pagination', function() {
	  return function(input, currentPage, pageSize) {
	    if(angular.isArray(input)) {
	      var start = (currentPage-1)*pageSize;
	      var end = currentPage*pageSize;
	      return input.slice(start, end);
	    }
	  };
	});

/*$(document).on('click', 'a.popup-dialog', function(e) {
    e.preventDefault();
    $.contratLookup('ajax.php/' + $(this).attr('href').substr(1), function (contrat) {
        var url = window.location.href;
        if (contrat && contrat.id)
            url = 'contrat.php?id='+contrat.id;
        $.pjax({url: url, container: '#pjax-container'})
        return false;
     });

    return false;
});*/

</script>
