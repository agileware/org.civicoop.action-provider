(function(angular, $, _) {
  // example: <input name="subject" /> <input crm-mailing-token on-select="doSomething(token.name)" />
  // WISHLIST: Instead of global CRM.crmMailing.mailTokens, accept token list as an input
  angular.module('action_provider').directive('crmApToken', function() {
    return {
      restrict: 'E',
      require: '^crmUiIdScope',
      scope: {
        onSelect: '@',
        tokens: '@',
      },
      template: '<input type="text" class="crmMailingToken" />',
      link: function(scope, element, attrs, crmUiIdCtrl) {
        var tokens = angular.copy(scope.tokens);
        if (typeof tokens === 'string' || tokens instanceof String) {
          tokens = JSON.parse(tokens);
        }
        console.log(tokens);
        $(element).addClass('crm-action-menu fa-code').crmSelect2({
          width: "12em",
          dropdownAutoWidth: true,
          data: tokens,
          placeholder: ts('Tokens')
        });
        $(element).on('select2-selecting', function(e) {
          e.preventDefault();
          $(element).select2('close').select2('val', '');
          scope.$parent.$eval(attrs.onSelect, {
            token: {name: e.val}
          });
        });
      }
    };
  });
})(angular, CRM.$, CRM._);
