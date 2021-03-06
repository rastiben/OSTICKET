moment.locale('fr');

//var Appli = angular.module('Appli',['ngRoute','ngMaterial','ngResource'])
angular.module('myApp').requires.push('ngRoute');
angular.module('myApp').requires.push('ngMaterial');
angular.module('myApp').requires.push('ngResource');

app.config(function($routeProvider, $locationProvider, $mdDateLocaleProvider) { // ROUTE PROVIDER (Configuration des adressses)
    $routeProvider
    .when('/osticket/upload/scp/pret.php', {
        templateUrl: '/osticket/upload/scp/view/stocks.html', // ADRESSE LISTE
        controller: 'stocksController'
    })
    .when('/osticket/upload/scp/pret.php/:id', {
        templateUrl: '/osticket/upload/scp/view/stock.html', // ADRESSE HISTORIQUE
        controller: 'stockController'
    })
    .otherwise({
        controller : function(){
          console.log(window.location.href);

            window.location.replace(window.location.href);
        },
        template : "<div></div>"
    }); // ADDRESSE PRIMAIRE

    $mdDateLocaleProvider.formatDate = function(date) {
      return moment(date).format('DD/MM/YYYY');
    };

    $locationProvider.html5Mode(true); // CE CODE PERMET DE MODIFIER LES ADDREESE EN HTML POUR NE PAS AVOIR /localhost/#! MAIS /localhost/
})

.factory('stocksFactory',['$resource', function($resource){ //MODULE

    return $resource('/osticket/upload/scp/ajax.php/stocks/:id', {id:'@id'}
        , {
            historiques:{
                method: 'GET' // Initialisation de la method PUT pour Mettre à jour les informations du materiel
                , params: {id:'@id'} // Parametre id pour le lien si dessous
                , url: '/osticket/upload/scp/ajax.php/stocks/:id/historiques' // Lien de la page ou doit agir la methode PUT
                , isArray: true
            },
            addHistorique:{
                method: 'POST' // Initialisation de la method PUT pour Mettre à jour les informations du materiel
                , params: {id:'@id'}
                , url: '/osticket/upload/scp/ajax.php/stocks/:id/historiques' // Lien de la page ou doit agir la methode PUT
            }
        }
    )

}])


//CONTROLLEUR QUI PERMET DANS TRIER DE A a Z (PARTIE STOCKS)
.controller('stocksController',['$scope','stocksFactory', '$log','$location', 'orderByFilter', '$timeout', function($scope, stocksFactory, $log, $location, orderBy, $timeout){
    $scope.stocks = stocksFactory.query();
    $scope.err = undefined;

    $scope.currentStock = undefined;
    $scope.propertyName = "dispo";  // LE PROPERTY NAME EST DESIGNATION
    $scope.reverse  = true;              // IL EST DE BASE FAUX
    $scope.modalOpen = false;

    $scope.delete = function(stock){
        stocksFactory.delete({id:stock.id},function(){
            var index = $scope.stocks.indexOf(stock);
            $scope.stocks.splice(index,1);
            $('.modal-backdrop').remove();
        });
    }

//###############################################################################################

    $scope.addForm = false;

    $scope.displayForm=function() {
        $scope.addForm=!$scope.addForm;
    }

   $scope.addLine = function(index) {
      var stock = new stocksFactory();
      stock.designation = $scope.designation;
      stock.categorie = $scope.categorie;
      stock.marque = $scope.marque;
      stock.numserie = $scope.numserie;
      stock.dispo = $scope.dispo;
      stock.$save(function(){
        $scope.stocks.push(stock);
        $scope.err = undefined;
      },function(err){
        $scope.err = err.data;
      });
    }

    $scope.sortBy = function(propertyName) {
            $scope.reverse  = (propertyName !== null && $scope.propertyName === propertyName) //ON POSE UN QUESTION (?) EST CE QUE PROPERTYNAME N'EST PAS                                                                                   // NUL
            ? !$scope.reverse  : true;                                                        //ET QUE C'EST DESIGNATION ALORS L'INVERSE IL RESTE FAUX
            $scope.propertyName = propertyName;
    };

    $scope.modalInfo = function(stock){
      $scope.currentStock = stock;
      $scope.modalOpen = true;
    }

    $scope.goTo = function(id){
      if(!$scope.modalOpen)
        $location.path("/osticket/upload/scp/pret.php/"+id);

      $scope.modalOpen = false;
    }

}])


.directive('modal', function () {           // DIRECTIVE PERMETTANT LA VERIFICAATION DE LA SUPPRESION D'UN MATERIEL
    return {
        scope: {
            stock: '=',                    // Données de stock pour indiquer la designation de l'objet et son numserie de serie
            title: '=modalTitle',
            header: '=modalHeader',
            body: '=modalBody',
            footer: '=modalFooter',
            callbackbuttonright: '&ngClickRightButton',
            handler: '=name'
        },
        backdrop: 'static',
        templateUrl: 'osTicket/upload/scp/view/modal.html',         // Lien du Modal
        controller: function ($scope) {
            $scope.handler = 'pop';
            $scope.remove = function(){
              $scope.callbackbuttonright();
              $(".modal-backdrop").hide();
            }
        },
        link(scope,element,attrs){                              // Supression forcé du Background
            scope.element = element;
                //element.html('');
                //$('.modal-backdrop.fade.in').remove();

        }
    };
})




//CONTROLEUR QUI PERMET D'ALLER DANS L'ID (PARTIE STOCK)
.controller('stockController',['$scope','stocksFactory', '$log', 'orderByFilter', '$routeParams', '$filter', function($scope, stocksFactory, $log,  orderBy, $routeParams, $filter){
    var id = $routeParams.id;
    $scope.stock = stocksFactory.get({'id':id},function(){
      $scope.message = $scope.stock.dispo === 1 ? 'DISPONIBLE' : 'NON DISPONIBLE';
    }); // FILTRAGE DU MODULE STOCKSFACTORY (soit 1 ; 2 ; 3) POUR FAIRE CORRESPONDRE

    $scope.historiques = stocksFactory.historiques({'id':id});  // L'HISTORIQUE AVEC LE MATERIEL (stock/1 = info objet 1)

    $scope.data = {
      cb1:true
    };

    $scope.setMessage = function(){
      $scope.message = 'DISPONIBLE';
      $scope.stock.dispo = 1;
      $scope.stock.thread_entry_id = null;
      $scope.stock.$save();
    }


    this.myDate = new Date(); // Code du calendrier (ici initialisation d'une date)
    this.isOpen = false;      // de base le calendrier n'est pas ouvert
//###############################################################################################

    $scope.addForm = false;

    $scope.displayInfo=function() {
        $scope.addForm=!$scope.addForm;
    }

    $scope.addInfo = function() {

      $scope.info.push({
            id:$scope.id,
            designation:$scope.designation,
            categorie:$scope.categorie,
            marque:$scope.marque,
            numserie:$scope.numserie,
      });

     }

      $scope.master = {};

      $scope.update = function(designation) {
        $scope.master = angular.copy(designation);
      }

      $scope.reset = function() {
        $scope.designation = angular.copy($scope.master);
      }

      $scope.reset();






//###############################################################################################

    $scope.addForm2 = false;

    $scope.displayHisto=function() {
        $scope.addForm2=!$scope.addForm2;
    }

    $scope.date = moment();

    $scope.addHisto = function() {
        var historique = new stocksFactory();
        historique.org = $scope.org;
        historique.stock_id = $scope.stock.id;
        historique.destinataire = $scope.destinataire;
        historique.visa = $scope.visa;
        historique.raison = $scope.raison;
        historique.date = $scope.date;
        stocksFactory.addHistorique({id:$scope.stock.id},historique,function(historique){
          $scope.addForm2 = false;
          $scope.historiques.unshift(historique);

          $scope.message = 'NON DISPONIBLE';
          $scope.stock.dispo = 0;
          $scope.stock.thread_entry_id = null;
          $scope.stock.$save();
        });
    }

    $scope.data = {
    cb1: true,
    };


    $scope.printHistorique = function(historique){
      var loadFile = function (url, callback) {
            JSZipUtils.getBinaryContent(url, callback);
        }
        loadFile("osTicket/upload/assets/doc/pret.docx", function (err, content) {
            if (err) {
                throw err
            };
            var zip = new JSZip(content);
            var doc = new Docxtemplater().loadZip(zip)
            //doc.attachModule(imageModule);
            doc.setData({
                    "client": historique.org
                    , "destinataire": historique.destinataire
                    , "designation": $scope.stock.designation
                    , "marque": $scope.stock.marque
                    , "numeroSerie": $scope.stock.numserie
                    , "datePret": moment(historique.date).format('DD/MM/YYYY')
                    , "raison": historique.raison
                    , "visa": historique.visa
                }) //set the templateVariables
            doc.render() //apply them (replace all occurences of {first_name} by Hipp, ...)
            out = doc.getZip().generate({
                    type: "blob"
                }) //Output the document using Data-URI
            saveAs(out, "Prêt " + $scope.stock.designation + " " + historique.org + ".docx")
        });
    }

   $scope.origData = angular.copy($scope.stock);

    $scope.reset = function () {
       angular.copy($scope.origData, $scope.stock);
    }

}]);
