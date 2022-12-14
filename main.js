//Nesletter Ajax Submit
function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
};

function newsletter_check(){
    return false;
}

jQuery(function($){
    /* Newsletter support */
    $('form.gdlr-core-newsletter-form')
    .attr('novalidate', true)
    .each( function() {
        var $this = $(this),
        $input = $this.find( 'input[name="ne"]'),
        $noti = $input.prev(),
        $submit = $this.find( 'input[type="submit"]'),

        success = function() {
            if($('.newsletter-alert').length > 0) {
                $('form.gdlr-core-newsletter-form .gdlr-core-newsletter-submit').after('<div class="alert alert-success p-2 mt-2 newsletter-alert">Başarılı bir şekilde abone oldunuz.</div>');
            } else {
                $('.newsletter-alert').text('Başarılı bir şekilde abone oldunuz.');
            }
        };

        // Submit handler
        $this.submit( function(e) {
            var serializedData = $this.serialize();
            $noti = $input.prev();
            e.preventDefault();
            // validate
            if( validateEmail( $input.val() ) ) { 
                var data = {};
                data = {
                    action: 'newsletter_ajax_subscribe',
                    nonce: ajax.nonce,
                    data: serializedData
                }

                // send ajax request
                $.ajax({
                    method: "POST",
                    url: ajax.url,
                    data: data,
                    beforeSend: function() {
                        //$input.prop( 'disabled', true );
                        //$submit.val('Gönderiliyor...').prop( 'disabled', true );
                    },
                    success: function( data ) {
                        console.log(data);
                        console.log(data.status);
                        if( data.status == 'success' ) {
                            //success();
                            $('.newsletter-alert').remove();
                            $('form.gdlr-core-newsletter-form .gdlr-core-newsletter-submit').after('<div class="alert alert-success p-2 mt-2 newsletter-alert">'+data.msg+'</div>');
                        } else {
                            $('.newsletter-alert').remove();
                            $('form.gdlr-core-newsletter-form .gdlr-core-newsletter-submit').after('<div class="alert alert-danger p-2 mt-2 newsletter-alert">'+data.msg+'</div>');
                        }
                        $input.prop( 'disabled', false );
                        $submit.val('Abone Ol').prop( 'disabled', false );
                    }
                });
            } else {
                $('.newsletter-alert').remove();
                $('form.gdlr-core-newsletter-form .gdlr-core-newsletter-submit').after('<div class="alert alert-danger p-2 mt-2 newsletter-alert">Lütfen geçerli bir e-posta adresi girin!</div>');
            };
        });
    });
});
//Nesletter Ajax Submit ~