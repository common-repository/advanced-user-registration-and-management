jQuery(document).ready(function ($) {
    
enableHostedPage();
 $('#lr-hosted-enable').change(function() {
            enableHostedPage();
    });


    //tabs
    $('.lr-options-tab-btns li').click(function () {
        var tab_id = $(this).attr('data-tab');

        $('.lr-options-tab-btns li').removeClass('lr-active');
        $('.lr-tab-frame').removeClass('lr-active');

        $(this).addClass('lr-active');
        $("#" + tab_id).addClass('lr-active');
    });
     lrCheckValidJson();
});
jQuery(function ($) {

    function hideAndShowElement( element, inputBoxName ) {
        if ( element.is(':checked') ) {
            $(inputBoxName).hide();
        } else {
            $(inputBoxName).show();
        }
    }
    function showAndHideElement( element, inputBoxName ) {
        if ( element.is(':checked') ) {
            $(inputBoxName).show();
        } else {
            $(inputBoxName).hide();
        }
    }

    function selectOne( changedElement, secondElement ) {
        if( changedElement.is(':checked') ) {
            if ( secondElement.is(':checked') ) {
                $( secondElement );
            }
        }
    }

    function showAndHideVerifyOption( option ) {
        $('.ver-opt').hide();
        $('.ver-opt input').prop('disabled', true);
        $('.ver-opt textarea').prop('disabled', true);

        if ( 'required' == option ) {
            $('.ver-opt.ena').show();
            $('.ver-opt.ena input').prop('disabled', false);
            $('.ver-opt.ena textarea').prop('disabled', false);
        }
        if ( 'optional' == option ) {
            $('.ver-opt.opt').show();
            $('.ver-opt.email-temp').show();
            $('.ver-opt.opt input').prop('disabled', false);
            $('#lr-email-verify-template').prop('disabled', false);
            $('.ver-opt.opt textarea').prop('disabled', false);
        }

        if ( 'disabled' == option ) {
            $('.ver-opt.dis').show();
            $('.ver-opt.dis input').prop('disabled', false);
        }
    }

    // Hide/Show Options if enabled/disabled on change
    $('#lr-raas-autopage').change(function() {
            hideAndShowElement( $(this), '.lr-custom-page-settings' );
    });
    $('#lr-v2captcha-enable').change(function() {
            showAndHideElement( $(this), '.lr-v2captcha-key' );
    });
    hideAndShowElement( $('#lr-raas-autopage'), '.lr-custom-page-settings' );

    showAndHideElement( $('#lr-v2captcha-enable'), '.lr-v2captcha-key' );

    showAndHideVerifyOption( $('input:radio[name="LR_Raas_Settings[email_verify_option]"]:checked').val() );

    $('input:radio[name="LR_Raas_Settings[email_verify_option]"]').change( function() {
        showAndHideVerifyOption( $(this).val() );
    }) 


});

function lrCheckValidJson() {
    jQuery('#lr-custom-options').blur(function(){
        var profile = jQuery('#lr-custom-options').val();
        var response = '';
        try
        {
            response = jQuery.parseJSON(profile);
            if(response != true && response != false){
                var validjson = JSON.stringify(response, null, '\t').replace(/</g, '&lt;');
                if(validjson != 'null'){
                    jQuery('#lr-custom-options').val(validjson);
                    jQuery('#lr-custom-options').css("border","1px solid green");
                }else{
                    jQuery('#lr-custom-options').css("border","1px solid red");
                }
            }
            else{
                jQuery('#lr-custom-options').css("border","1px solid green");
            }
        } catch (e)
        {
            jQuery('#lr-custom-options').css("border","1px solid green");
        }
    });
}


function enableHostedPage(){
    
    var hostedpage = jQuery('#lr-hosted-enable').is(':checked');

if(hostedpage === true){
    jQuery('#userRegistration,#email-options,#enable-linking').hide();
    } else {
      jQuery('#userRegistration,#email-options,#enable-linking').show();
    }

}
