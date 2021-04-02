(function(angular, $, _) {
  // Declare a list of dependencies.
  angular.module('action_provider', [
    'crmUi', 'crmUtil', 'ngRoute'
  ]);
  angular.module('action_provider').factory('actionProviderFactory', ["crmApi", "$q", function(crmApi, $q) {
    var actionTypes = {};

    // Initialize the context.
    var setContext = function (context) {
      if (!(context in actionTypes)) {
        actionTypes[context] = {};
      }
    }

    var retrieveAction = function (name, context) {
      setContext(context);
      if (!(name in actionTypes[context])) {
        var defer = $q.defer();
        crmApi('ActionProvider', 'getaction', {
          name: name,
          context: context
        }).then(function (data) {
          actionTypes[context][name] = data;
          defer.resolve(actionTypes[context][name]);
        });
        return defer.promise;
      }
      return $q.resolve(actionTypes[context][name]);
    };

    return {
      getAction: function (name, context) {
        return retrieveAction(name, context);
      },

      setAction: function(name, context, action) {
        setContext(context);
        actionTypes[context][name] = action;
      }
    };
  }]);
})(angular, CRM.$, CRM._);
