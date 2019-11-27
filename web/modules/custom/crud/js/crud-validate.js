(function ($, Drupal) {
  Drupal.behaviors.validate = {
    attach:function(context, settings){
      jQuery.validator.setDefaults({
        debug: true,
        success: "valid"
      });
      $( "#register-form" ).validate({
        rules: {
          user_name: {
            required: true,
            lettersonly: true
          },
        },
        messages: {
          user_name: {
            required: 'Ingrese el nombre del usuario',
            lettersonly: "Caracter invalido, ingrese solo letras"
          }
        }
      });
      jQuery.validator.addMethod("lettersonly", function(value, element) {
        return this.optional(element) || /^[a-z ]+$/i.test(value);
      });
    }
  };

})(jQuery, window.Drupal);
