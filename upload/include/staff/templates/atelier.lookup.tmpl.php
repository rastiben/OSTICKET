        <div class="modal fade" id="fichesModal">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">{{modalTitle}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <p>Modal body text goes here.</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.1/angular.min.js"></script>

    <script>
        var myApp = angular.module('myApp',[]);

        myApp.service('ficheSrvc', function($http) {
            delete $http.defaults.headers.common['X-Requested-With'];
            this.getData = function(callbackFunc) {
                $http({
                    method: 'GET',
                    url: 'https://www.example.com/api/v1/page',
                    params: 'limit=10, sort_by=created:desc',
                    headers: {'Authorization': 'Token token=xxxxYYYYZzzz'}
                }).success(function(data){
                    // With the data succesfully returned, call our callback
                    callbackFunc(data);
                }).error(function(){
                    alert("error");
                });
             }
        });

        myApp.controller('ficheCrtl', function($scope, ficheSrvc) {
            $scope.data = null;
            $scope.modalTitle = 'Modal Title';
            /*ficheSrvc.getData(function(dataResponse) {
                $scope.data = dataResponse;
            });*/
            $scope.posteClicked = function($id){
                $scope.modalTitle = $id;
                $('#fichesModal').modal('toggle');
            }
        });

    </script>
