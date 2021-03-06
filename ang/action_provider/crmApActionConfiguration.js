(function(angular, $, _) {
  // "crmApActionConfiguration" is a basic skeletal directive.
  // Example usage: <crm-ap-action-configuration configuration="action.configuration" action="action.type"></crm-ap-action-configuration>
  angular.module('action_provider').directive('crmApActionConfiguration', ["actionProviderFactory", function(actionProviderFactory) {
    var actions = {};
    return {
      restrict: 'E',
      templateUrl: '~/action_provider/crmApActionConfiguration.html',
      scope: {
        name: '=',
        context: '@',
        configuration: '=',
        fields: '=?',
        mapping: '=?',
      },
      link: function($scope, $el, $attr) {
        var getCrmUiField = function(spec) {
          var crmUiField = {
            'name': spec.name,
            'title': spec.title
          };
          if (spec.required) {
            crmUiField.required = true;
          }
          return crmUiField;
        };

      	$scope.ts = CRM.ts(null);
      	$scope.action = {};
      	$scope.uiFields = {};

      	if (!($scope.context in actions)) {
      	  actions[$scope.context] = {};
      	}

      	if ($scope.name in actions[$scope.context]) {
      	  $scope.action = actions[$scope.context][$scope.name];
      	  return;
      	}

        actionProviderFactory.getAction($scope.name, $scope.context)
        .then(function (data) {
      	  actions[$scope.context][$scope.name] = data;
      	  $scope.action = data;

          for (var spec in $scope.action.configuration_spec) {
            $scope.action.configuration_spec[spec].crmUiField = getCrmUiField($scope.action.configuration_spec[spec]);
            if ($scope.action.configuration_spec[spec].options) {
              $scope.action.configuration_spec[spec].select2Options = [];
              for (var optionValue in $scope.action.configuration_spec[spec].options) {
                var select2Option = {
                  'id': optionValue,
                  'text': $scope.action.configuration_spec[spec].options[optionValue],
                };
                $scope.action.configuration_spec[spec].select2Options.push(select2Option);
              }
            }
          }

          for (var parameterSpec in $scope.action.parameter_spec) {
            if ($scope.action.parameter_spec[parameterSpec].type == 'group') {
              for (var subParameterSpec in $scope.action.parameter_spec[parameterSpec].specification_bag) {
                $scope.action.parameter_spec[parameterSpec].specification_bag[subParameterSpec].crmUiField = getCrmUiField($scope.action.parameter_spec[parameterSpec].specification_bag[subParameterSpec]);
              }
            } else {
              $scope.action.parameter_spec[parameterSpec].crmUiField = getCrmUiField($scope.action.parameter_spec[parameterSpec]);
            }
          }

      	});
      }
    };
  }]);
})(angular, CRM.$, CRM._);
