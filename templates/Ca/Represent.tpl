{literal}
<script type="text/javascript">
CRM.$(function($) {
  $("input[name='postal_code-Primary']").blur( function() {
    var params = {
      "street_address": "street_address-Primary",
      "city": "city-Primary",
      "country": "country-Primary",
      "state_province": "state_province-Primary",
      "state_province_id": "state_province-Primary",
      "postal_code": "postal_code-Primary"
    };
    var address = {};
    $.each( params, function( key, value ) {
      if (key == "country" || key == "state_province" || key == "state_province_id") {
        address[key] = $("#" + value +" option:selected").val();
      }
      else {
        address[key] = $("#" + value).val();
      }
    });
    var geoCode = getGeocode(address);
  });

  function getGeocode(address) {
    if ($.isEmptyObject(address)) {
      alert("Please fill out the address fields to get a list of representatives.");
      return FALSE;
    }
    var dataUrl = {/literal}"{crmURL p='civicrm/represent' h=0 }"{literal};
    var geocode = $.ajax({
      url: dataUrl,
      method: 'POST',
      dataType: 'json',
      async: false,
      data: {
        address: address
      }
      }).responseText;
    var response = $.parseJSON(geocode);
    return response;
  }
});
</script>
{/literal}