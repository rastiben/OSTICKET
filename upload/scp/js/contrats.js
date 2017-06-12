moment.locale('fr');

angular.module('myApp').requires.push('ngResource');
angular.module('myApp').requires.push('ngMaterial');

app.factory('contratFactory',['$resource',function($resource){

  return $resource('ajax.php/contrats/:contratId',{contratId:'@id'});

}]);

app.controller('contratCtrl',['$scope','contratFactory','$log','$http',function($scope,contratFactory,$log,$http){

  $scope.header = "Ajout d'un contrat";
  $scope.valid = "Créer le contrat";
  $scope.doInvoice = false;
  $scope.invoiceLoading = false;

  $scope.initContratData = function(contrat){
    /*init date*/
    contrat.date_debut = moment(contrat.date_debut,'YYYY-MM-DD').format('DD/MM/YYYY');
    contrat.date_fin = moment(contrat.date_fin,'YYYY-MM-DD').format('DD/MM/YYYY');
    contrat.created = moment(contrat.created,'YYYY-MM-DD').format('DD/MM/YYYY');
    contrat.date_prochaine_facture = moment(contrat.date_prochaine_facture,'YYYY-MM-DD').format('DD/MM/YYYY');
    //$scope.calcPrice();
  };

  $scope.calcPrice = function(){
    var prixTotal = 0;
    angular.forEach($scope.filteredContrats,function(contrat,key){
      prixTotal = parseFloat(prixTotal) + parseFloat(contrat.prix);
    });
    return prixTotal;
  };

  $scope.contrats = contratFactory.query(function(contrats){
    angular.forEach(contrats,function(contrat,key){
      $scope.initContratData(contrat);
    });
  });

  $scope.propertyName = 'code';
  $scope.reverse = false;

  $scope.sortBy = function(propertyName) {
    $scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : true;
    $scope.propertyName = propertyName;
  };

  /*$scope.$watch('filteredContrats', function(newValue, oldValue) {
    //console.log(newValue);
  });*/

  $scope.save = function(vars){
    if($scope.action == "create")
      var contrat = new contratFactory();
    else
      var contrat = angular.copy($scope.contrat);

      /*Informations*/
      contrat.code = vars['code'];
      contrat.org = vars['org'];
      contrat.client = vars['client'];
      contrat.etat = vars['etat'];
      contrat.date_debut = moment(vars['date_debut'],'DD/MM/YYYY').format('YYYY-MM-DD');
      contrat.date_fin = moment(vars['date_fin'],'DD/MM/YYYY').format('YYYY-MM-DD');
      contrat.type = vars['type'];
      contrat.comments = vars['comments'];

      /*Facturation*/
      contrat.periodicite = vars['periodicite'];
      contrat.date_prochaine_facture = moment(vars['date_prochaine_facture'],'DD/MM/YYYY').format('YYYY-MM-DD');
      contrat.tva = vars['tva'];
      contrat.compte_compta = vars['compte_compta'];
      contrat.prix = vars['prix'];

      contrat.$save(function(contrat){
        $scope.initContratData(contrat);
        if($scope.action == "create")
          $scope.contrats.push(angular.copy(contrat));
      });
    }

    $scope.cancel = function(){
      //$scope.contrat = $scope.originalContrat;
      angular.forEach($scope.originalContrat,function(value,key){
        $scope.contrat[key] = value;
      });
    }

    $scope.remove = function(contrat){
      contrat.$remove(function(){
        var index = $scope.contrats.indexOf(contrat);
        $scope.contrats.splice(index,1);
      });
    }

    $scope.facturer = function(){
      $scope.doInvoice = true;
      $scope.createdFilter = moment().add(1,'M').format('MM/YYYY');
    }

    $scope.printList = function(){
      /*Impression sous le format EXCEL*/
      var toPrint = [];
      var toKeep = ['code','org','date_debut','date_fin','type','prix','date_prochaine_facture'];

      angular.forEach($scope.filteredContrats,function(contrat,keyC){
        var temp = {};
        angular.forEach(contrat,function(value,property){
          if(toKeep.includes(property)){
            if(property == "prix")
              value = parseFloat(value).toFixed(2);
            temp[property.charAt(0).toUpperCase() + property.replace(/_/g,' ').slice(1)] = value;
          }
        });
        toPrint.push(temp);
      });

      var opts = {headers:true};
      alasql('SELECT * INTO XLSX("Facturation_Contrats_'+moment().format('MMYYYY')+'.xlsx",?) FROM ?',[opts,toPrint]);
    }

    $scope.invoice = function(){

      $scope.invoiceLoading = true;
      //modification de toutes les dates associée à la facture
      //Calcule des nouvelles date de renouvellement du contrat.
      angular.forEach($scope.filteredContrats,function(value,key){
        var momentDateProchaineFacture = moment(value.date_prochaine_facture,"DD/MM/YYYY");
        var momentDateDebut = moment(value.date_debut,"DD/MM/YYYY");
        var momentDateFin = moment(value.date_fin,"DD/MM/YYYY");
        var month = 0;
        switch (value.periodicite) {
          case "Annuelle":
          month = 12;
            break;
          case "Semestrielle":
          month = 6;
            break;
          case "Trimestrielle":
          month = 3;
            break;
          case "Mensuelle":
          month = 1;
            break;
        }

        if(momentDateFin.isBefore(momentDateProchaineFacture)){
          var difference = momentDateFin.diff(momentDateDebut);
          value.date_debut = momentDateDebut.add(12,"M").format('DD/MM/YYYY');
          value.date_fin = momentDateDebut.add(difference).format('DD/MM/YYYY');
        }

        value.date_prochaine_facture = momentDateProchaineFacture.add(month,"M").format("DD/MM/YYYY");
        //momentDateProchaineFacture = moment(value.date_prochaine_facture,"DD/MM/YYYY");
      });

      $http.post('./ajaxs.php/docSage/contrats',{contrats:JSON.stringify($scope.filteredContrats)},{headers: {'Content-Type': 'application/json'}})
      .then(function(){
        $scope.invoiceLoading = false;
      });

      $scope.doInvoice = false;
      $scope.createdFilter = '';

    }

    $scope.modalInfo = function(header,valid,contrat,action){
      $scope.header = header;
      $scope.valid = valid;
      $scope.contrat = contrat == null ? new contratFactory() : contrat;
      $scope.originalContrat = angular.copy($scope.contrat);

      if($scope.contrat.compte_compta == undefined)
        $scope.contrat.compte_compta = '706760';

      $scope.action = action;
    }

}]);

app.directive('datepicker', function() {
  return {
    require: 'ngModel',
    link: function(scope, el, attr, ngModel) {
      $.datepicker.regional['fr'] = {
    		closeText: 'Fermer',
    		prevText: 'Précédent',
    		nextText: 'Suivant',
    		currentText: 'Aujourd\'hui',
    		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
    		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
    		monthNamesShort: ['Janv.','Févr.','Mars','Avril','Mai','Juin',
    		'Juil.','Août','Sept.','Oct.','Nov.','Déc.'],
    		dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
    		dayNamesShort: ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'],
    		dayNamesMin: ['D','L','M','M','J','V','S'],
    		weekHeader: 'Sem.',
    		dateFormat: 'dd/mm/yy',
    		firstDay: 1,
    		isRTL: false,
    		showMonthAfterYear: false,
    		yearSuffix: ''};
    	$.datepicker.setDefaults($.datepicker.regional['fr']);
      var datepicker = $(el).datepicker({
          startView: 1,
          dateFormat: 'dd/mm/yy',
          autoclose: true
      });
      if($(el).attr('id') == "date_debut"){
        datepicker.on("input change",function(e){
          scope.$apply(function(){
            scope.contrat.date_fin = moment(scope.contrat.date_debut,'DD/MM/YYYY').add(1,'y').subtract(1,'d').format('DD/MM/YYYY');
            if(scope.contrat.org != undefined)
              scope.contrat.code = moment(scope.contrat.date_debut,'DD/MM/YYYY').format('YYYYMM') + scope.contrat.org;
            scope.contrat.date_prochaine_facture = scope.contrat.date_debut;
          });
        });
      }
    }
  };
});

app.directive('modal',['$http','contratFactory', function ($http,contratFactory) {
    return {
        restrict: 'EA',
        scope: {
            header: '@',
            body: '@',
            footer: '@',
            validButton : '@valid',
            contrat : '=',
            permissions : '=',
            callbackbuttonright: '&ngClickRightButton',
            callbackbuttonleft:'&ngClickLeftButton',
            callremove:'&callRemove',
            handler: '=lolo'
        },
        templateUrl: './templates/modal.html',
        transclude: true,
        controller: function ($scope) {
            $scope.handler = 'pop';
            $scope.multiply = function($event){
              if($event.keyCode == 13)
                if($scope.contrat.prix != undefined)
                  $scope.contrat.prix = (parseFloat($scope.contrat.prix) + ($scope.contrat.prix * ($event.target.value/100))).toFixed(2);
            }
        },
        link: function(scope, element, attrs){
          element.bind('keydown', function(evt) {
            if (evt.key == "Enter") {
                evt.preventDefault(); // Doesn't work at all
                window.stop(); // Works in all browsers but IE
                document.execCommand("Stop"); // Works in IE
                return false; // Don't even know why it's here. Does nothing.
            }
          });
          $http.get('ajaxs.php/contrats/typeahead').then(function(data){
            scope.contratTypes = data.data;
            $("#type #default").remove();
          });
        }
    };
}]);

app.directive('typeahead', function () {
  return {
    restrict: 'A',
    scope: {
      url:'=',
      contrat:'='
    },
    link: function(scope, element, attrs){
      $(element).typeahead({
          source: function (typeahead, query) {
              $.ajax({
                  url: scope.url + $(element).val(),
                  dataType: 'json',
                  success: function (data) {
                      typeahead.process(data);
                  }
              });
          },
          onselect: function (obj) {
            scope.$apply(function(){
              scope.contrat.org = obj.id;
              if(scope.contrat.date_debut != undefined)
                scope.contrat.code = moment(scope.contrat.date_debut,'DD/MM/YYYY').format('YYYYMM') + scope.contrat.org;
            });
          },
          property: "/bin/true"
      });
    }
  }
});



//filtre de formatage de moment date
app.filter('mFormat', function() {
    return function(input, format) {
      return (!!input) ? input.format(format) : '';
    }
});

app.filter('havePerms', function() {
    return function(input, perms) {
      return input[perms] != undefined ? true : false;
    }
});
