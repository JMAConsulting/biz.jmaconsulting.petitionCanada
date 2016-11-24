{if $duplicate == 'confirmed'}
  If you don't find it, check your spam folder.
{/if}

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
        var selectval = $("#" + value +" option:selected").val();
        if (typeof selectval !== 'undefined') {
          address[key] = selectval;
        }
      }
      else {
        var textval = $("#" + value).val();
        if (typeof textval !== 'undefined') {
          address[key] = textval;
	}
      }
    });
    var geocode = getGeocode(address);
    if (!geocode) {
      geocode = address;
    }
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
    if (geocode) {
      var response = $.parseJSON(geocode);
      return response;
    }
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
            trHTML += '<dl><dt><strong>';
	    if (item.url) {
              trHTML += '<a href='+ item.url +'>';
            }
	    trHTML += item.display_name;
	    if (item.url) {
	      trHTML += '</a>';
            }
            trHTML += '</strong></dt><dd><span>';
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

<style>
#crm-container.crm-public .label {ldelim}
    font-size: 18px;
{rdelim}

</style>
