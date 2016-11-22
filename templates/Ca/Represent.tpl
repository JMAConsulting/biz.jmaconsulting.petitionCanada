{if !$duplicate}
<div id="draft_email" class="crm-section editrow_draft_email-section form-item">
  <div class="label">
    <label for="draft_email">{$form.draft_email.label}</label>
  </div>
  <div class="content">
    {$form.draft_email.html}
  </div>
  <div class="content">
    {$form.is_subscribe.html}&nbsp;{$form.is_subscribe.label}
  </div>
  <div class="clear"></div>
</div>
{/if}

{literal}
<script type="text/javascript">
CRM.$(function($) {
  $("#draft_email").insertBefore(".crm-submit-buttons");
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
    var reps = {};
    $.each( params, function( key, value ) {
      if (key == "country" || key == "state_province" || key == "state_province_id") {
        address[key] = $("#" + value +" option:selected").val();
      }
      else {
        address[key] = $("#" + value).val();
      }
    });
    var geocode = getGeocode(address);
    getRepresentatives(geocode);
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

  function getRepresentatives(geocode) {
    $body = $("#representatives");
    var repEmails = [];
    $("input[name='representative_emails']").val('');
    var dataUrl = {/literal}"{crmURL p='civicrm/getrepresentatives' h=0 }"{literal};
    $body.addClass("dataTables_processing");
    $.ajax({
      url: dataUrl,
      method: 'POST',
      dataType: 'json',
      data: {
        geocode: geocode
      },
      success: function(data) {
        if (data) {
          var trHTML = '';
          $.each(data, function (i, item) {
	    repEmails.push(item.email);
            trHTML += '<dl><dt><strong><a href='+ item.url +'>' + item.name + '</a></strong></dt>';
            trHTML += '<dd><span>';
	    if (item.party_name) {
              trHTML += item.party_name;
            }
            if (item.party_name && item.elected_office) {
              trHTML += ', ';
            }
	    if (item.elected_office) {
              trHTML += item.elected_office;
            }
            trHTML += '</span>';
	    if (item.email) {
              trHTML += ' (<a href=mailto:'+ item.email +'>' + item.email + '</a>)';
            }
	    trHTML += '</dd></dl>';
          });
    	  $("input[name='representative_emails']").val(repEmails.join());
        }
        else {
          trHTML = '<div><span><h3 align-text="center">No Representatives found!</h3></span></div>';
        }
        $body.html(trHTML);
      },
      complete: function(){
        $body.removeClass("dataTables_processing");
      }
    });
  }
});
</script>
{/literal}