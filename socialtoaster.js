/*
 * Function that goes through all form fields and makes an associative array of their names and values
 * Ignores hidden fields and submit buttons
 */
function socialtoaster_submit_lead_form(formname) {

  var $j = jQuery.noConflict();
  var $inputs = $j('#'+formname+' :input')

  var formvalues = {};
  $inputs.each(function() {
      if( this.type != 'hidden' && this.type != 'submit' ) {
      	var fieldname = "";
      	// Wordpress's form puts the field names in the form of formBuilderForm[<fieldname>]
      	// The following code strips out the formBuilderForm[] part
      	fieldname = this.name.replace('formBuilderForm[', '').replace(']', '');
        formvalues[fieldname] = escape($j(this).val());
      }
  });

  st_promote_lead(formvalues, function() { document.getElementById(formname).submit() });
  return false;
}



