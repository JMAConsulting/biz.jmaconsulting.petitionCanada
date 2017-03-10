{if $duplicate == 'confirmed'}
  If you don't find it, check your spam folder.
{/if}

{if !$duplicate}
<div id="draft_email_block" class="crm-section editrow_draft_email-section form-item">
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
  $("#draft_email_block").insertBefore(".crm-submit-buttons");

  {/literal}{if !$contact_id}{literal}
    limitFunctions();
  {/literal}
  {/if}
  {literal}
  CKEDITOR.config.contentsCss = '{/literal}{$cssURL}{literal}';

  if ($("input[name='representative_emails']").val()) {
    address = getAddress();
    var geocode = getGeocode(address);
    if (!geocode) {
      geocode = address;
    }
    getRepresentatives(geocode);
  }

  $("input[name='postal_code-Primary'], input[name='city-Primary'], input[name='street_address-Primary']").blur( function() {
    var address = getAddress();
    var geocode = getGeocode(address);
    if (!geocode) {
      geocode = address;
    }
    getRepresentatives(geocode);
  });

  $("input[name='country-Primary'], input[name='state_province-Primary']").change( function() {
    var address = getAddress();
    var geocode = getGeocode(address);
    if (!geocode) {
      geocode = address;
    }
    getRepresentatives(geocode);
  });

  function limitFunctions() {
    var editor = CKEDITOR.instances['draft_email'];
    if (editor) {
      editor.destroy(true);
    }
    CKEDITOR.replace( 'draft_email',
    {
      toolbar : [
        { name: 'basicstyles', items : [ 'Bold','Italic' ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList' ] },
      ]
    });
  }

  function getAddress() {
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
    return address;
  }

  function getGeocode(address) {
    if ($.isEmptyObject(address)) {
      alert("Please fill out your address to add your local representatives as recipients.");
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
    var repNames = [];
    var repInfo = [];
    $("input[name='representative_emails']").val('');
    $("input[name='representative_names']").val('');
    var dataUrl = {/literal}"{crmURL p='civicrm/getrepresentatives' h=0 }"{literal};
    $body.addClass("blockUI blockOverlay");
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
	    repNames.push(item.display_name);
	    repInfo.push({
              email: item.email,
              name:  item.display_name,
              first_name:  item.first_name,
              last_name:  item.last_name,
              title: item.elected_office,
              party: item.party_name,
              district: item.district_name
            });
	    trHTML += '<dl><dt class="rep-names"><strong>' + item.display_name + '</strong>';
            if (item.elected_office) {
              trHTML += ' (' + item.elected_office + ') ';
            }
            trHTML += '</dt></dl>';
          });
    	  $("input[name='representative_emails']").val(repEmails.join());
    	  $("input[name='representative_names']").val(JSON.stringify(repInfo));
	  $('#email_frozen').text('Dear ' + repNames.join(", "));
        }
        else {
          trHTML = '<div><span><h3 align-text="center">No local representatives found.</h3></span></div>';
        }
        $body.html(trHTML);
      },
      complete: function(){
        $body.removeClass("blockUI blockOverlay");
      }
    });
  }
});
</script>
{/literal}
