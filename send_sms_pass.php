<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?> <script type="text/javascript" language="javascript">
 	function call_pass() {
 	  var msg   = $('#formx_pass').serialize();
        $.ajax({
          type: 'POST',
          url: '/sendmail/rek_pass.php',
          data: msg,
          success: function(data) {
            document.getElementById('psbutt').style.display="none";
            $('#results').html(data);
			$('#formx_pass')[0].reset();
			document.getElementById('popup-yes').style.display="block";
          },
			error:  function(xhr, str){
			alert('Возникла ошибка: ' + xhr.responseCode);
          }
        });
		
    }

//$('#rs_i').focusout(function(){
 //      var msg   = $('#formx_pass').serialize();
//        $.ajax({
 //         type: 'POST',
 //         url: '/fb/check_rek_pass.php',
//          data: msg,
//          success: function(data) {
//									
//									alert(data);
//									if(data=='suc'){
//														document.getElementById('no_rs').style.display="block";
//														document.getElementById('psbutt').style.display="none";
//													}
//									}          
//        });	

//});


</script>
<script>
// Popups.
$(document).on('click', '.open-popup', function (e) {
    e.preventDefault();


    $('.popups').fadeIn();
});

$(document).on('click', '.close-popup', function (e) {
    e.preventDefault();

    $('.popups').fadeOut();
});
</script> 
<style>

.popups .content .call-order form button
{
    *display: inline;
    *zoom: 1;
    display: -moz-inline-block!important;
    display: inline-block!important;
    vertical-align: middle!important;
    background: #dadada!important;
    background: -moz-linear-gradient(top, #74bc40, #74bc40)!important;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #74bc40), color-stop(100%, #74bc40))!important;
    background: -webkit-linear-gradient(top, #74bc40, #74bc40)!important;
    background: -o-linear-gradient(top, #74bc40, #74bc40)!important;
    background: -ms-linear-gradient(top, #74bc40, #74bc40)!important;
    background: linear-gradient(top, #74bc40, #74bc40)!important;
    cursor: pointer!important;
    #border-radius: 5px!important;
    border: 1px solid #999!important;
    height: 32px!important;
    padding: 0 25px!important;
    vertical-align: middle!important;
    text-align: center!important;
    font-size: 14px!important;
    color: white!important;
    vertical-align: middle!important;
    behavior: url(/bitrix/templates/citrus_tszh_blue/pie/PIE.php)!important;
}

.popups .content .call-order form button:hover
{
    background: #ccc!important;
    background: -moz-linear-gradient(top, #dadada, #fefefe)!important;
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #dadada), color-stop(100%, #fefefe))!important;
    background: -webkit-linear-gradient(top, #dadada, #fefefe)!important;
    background: -o-linear-gradient(top, #dadada, #fefefe)!important;
    background: -ms-linear-gradient(top, #dadada, #fefefe)!important;
    background: linear-gradient(top, #dadada, #fefefe)!important;
    behavior: url(/bitrix/templates/citrus_tszh_blue/pie/PIE.php)!important;
    color: #333;
}






</style>
<section id="popup-ps" class="popups">
<div class="content">
 <a href="index.html#" class="close-popup"> <img src="/bitrix/templates/citrus_tszh_blue/images/close-button.svg" alt=""> </a>
	<div class="call-order">
		<h2 class="block-title"> Запит отримання пароля</h2>
		<p>
			 Логін і пароль прийде в смс на вказаний номер телефону
		</p>
 <section class="feedback" id="goform" style="padding: 30px 0 55px!important;">
		<div class="wrap">
			<form id="formx_pass" action="javascript:void(null);" onsubmit="call_pass()" method="post">
 <span style="margin-left: 0px!important;">
				№О/Р <input id="rs_i" type="text" name="rs" placeholder="" required=""> </span> <span>
				Телефон <input type="tel" name="phone" placeholder="+38(098)2233299" required=""> </span> 
				<input type="hidden" name="action" value="support_form"> 
				<button type="submit" class="btnpass" id="psbutt">Зробити запит</button>
			</form>
		</div>
 </section>
		<p id="no_rs" style="display:none; color:red!important;">
			 Номер особового рахунку не знайденно зателефонуйте до контакт центру
		</p>
		<p class="little">
			 * № О/Р - Номер особового рахунку можливо знайти в квитанції на оплату від КК "Авікон"
		</p>
		<!--<p class="little">
			 * Відправляючи форму, ви даєте свою згоду на <a class="open-popup" href="#" data-popup="#popup-privacy">обробку персональних даних</a>
		</p>-->
	</div>
</div>
 </section>
 
 
 

