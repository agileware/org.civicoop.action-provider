(function(angular, $, _) {
  // "crmApConditionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-condition-configuration configuration="condition.configuration" condition="condition.type"></crm-ap-condition-configuration>
  angular.module('action_provider').directive('crmApConditionConfiguration', ["crmApi", function(crmApi) {
    var conditions = {};
    var actions = {};
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApConditionConfiguration.html',
      scope: {
        name: '=',
        action: '=',
        context: '@',
        configuration: '=',
        fields: '=?',
        mapping: '=?',
      },
      link: function($scope, $el, $attr) {
        $scope.ts = CRM.ts(null);
        $scope.condition = {};
        $scope.actionObject = {};

        if (!($scope.context in conditions)) {
          conditions[$scope.context] = {};
        }
        if (!($scope.context in actions)) {
          actions[$scope.context] = {};
        }

        $scope.$watch('name', function(newCondition, oldCondition) {
          if (!newCondition) {
            $scope.condition = null;
            return;
          }

          if ($scope.name in conditions[$scope.context]) {
            $scope.condition = conditions[$scope.context][$scope.name];
            return;
          }

          crmApi('ActionProvider', 'getcondition', {name: $scope.name, context: $scope.context}).
          then(function (data) {
            conditions[$scope.context][$scope.name] = data;
            $scope.condition = data;
          });
        });

        $scope.addItemToCollection = function addItemToCollection(specification) {
          console.log($scope.configuration);
          if (!$scope.configuration.parameter_mapping) {
            $scope.configuration.parameter_mapping = {};
          }
          if (!$scope.configuration.parameter_mapping[specification.name]) {
            $scope.configuration.parameter_mapping[specification.name] = [];
          }
          console.log($scope.configuration);
          $scope.configuration.parameter_mapping[specification.name].push({});
        };

        $scope.removeItem = function removeItem(item, specification) {
          var index = $scope.configuration.parameter_mapping[specification.name].indexOf(item);
          if (index >= 0) {
            $scope.configuration.parameter_mapping[specification.name].splice(index, 1);
          }
        };

        if ($scope.action in actions[$scope.context]) {
          $scope.actionObject = actions[$scope.context][$scope.action];
        } else {
          crmApi('ActionProvider', 'getaction', {
            name: $scope.action,
            context: $scope.context
          }).then(function (data) {
            actions[$scope.context][$scope.action] = data;
            $scope.actionObject = data;
          });
        }
      }
    };
  }]);
})(angular, CRM.$, CRM._);
