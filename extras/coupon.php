<html>
<head>
<title>Coupon Central</title>
<style type="text/css">
	body {
		font-size: 62.5%;
	}
	.ndlButton { 
		outline: 0; 
		margin:0 4px 0 0; 
		padding: .4em 1em; 
		text-decoration:none !important; 
		cursor:pointer; 
		position: relative; 
		text-align: center; 
		zoom: 1; 
	}
	#couponCode {
		width: 200px;
		margin-bottom: 8px;
	}
	#expireDate {
		width: 100px;
		margin-bottom: 8px;
	}
	#ruleSelector {
		width: 200px;
		margin-bottom: 8px;
	}
	#selectedDescription {
		width: 250px;
		height: 70px;
		overflow: hidden;
		padding: 3px;
		margin-top: 3px;
		margin-bottom: 3px;
		border: 1px #999999 solid;
		display: none;
		visibility: hidden;
	}
	#containerDiv {
		width: 270px;
		padding: 5px;
		overflow: hidden;
	}
 	.ui-widget h1, .ui-widget h2, .ui-widget h3, .ui-widget h4 {
		color: inherit;
		font-family: inherit;
	}
 	textarea.ui-state-disabled, input.ui-state-disabled {
		opacity:1 !important;
		filter:Alpha(Opacity=100) !important;
	}
</style>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/themes/cupertino/jquery-ui.css"
 type="text/css" rel="Stylesheet" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.min.js" type="text/javascript"></script>
<script type=application/javascript>
function resetForm()
{
	$('#submitButton').addClass('ui-state-disabled');
	$('#submitButton').attr('disabled', 'disabled');
	$('#couponCode').val($('#couponCode')[0].defaultValue);
	$("#expireDate").val(getExpireDate(0));
	$('#ruleSelector').val("none");
}
function checkReady()
{
//alert ($('#ruleSelector :selected').val() + " " + $('#couponCode').val() + " " + $('#couponCode')[0].defaultValue);
	if ($('#ruleSelector :selected').val() != "none" &&
		$('#couponCode').val() != $('#couponCode')[0].defaultValue)
	{
		$('#submitButton').removeClass('ui-state-disabled');
		$('#submitButton').removeAttr('disabled');
	}
	else
	{
		$('#submitButton').addClass('ui-state-disabled');
		$('#submitButton').attr('disabled', 'disabled');
	}
}
function getExpireDate(days)
{
	var expire = new Date();
	expire.setDate(expire.getDate() + parseInt(days))
	var month = expire.getMonth() + 1;
	var day = expire.getDate();
	return expire.getFullYear() + ((month<10)?"-0":"-") + month + ((day<10)?"-0":"-") + day;
}
$(document).ready(function(){  
	resetForm();
	$('#accordion').accordion({header: 'h3'});
	$('#accordion').accordion('activate', 2);
	$("#expireDate").val(getExpireDate(0));
	$("#expireDate").datepicker({
		showAnim: 'drop',
		dateFormat: 'yy-mm-dd',
		gotoCurrent: true,
		minDate: 0,
		maxDate: 14
	});
    $('#expireDays').change(function() {
		$('#expireDate').val(getExpireDate($('#expireDays :selected').val()));
	});
	$('.ndlButton').hover(
		function(){ 
			$(this).addClass("ui-state-hover"); 
		},
		function(){ 
			$(this).removeClass("ui-state-hover"); 
		}
	);
	$('#couponCode').focus(function() {
		if (this.value == this.defaultValue){
			this.value = '';
		}
		if(this.value != this.defaultValue){
			this.select();
		}
	});
	$('#couponCode').blur(function() {
		if ($.trim(this.value) == ''){
			this.value = (this.defaultValue ? this.defaultValue : '');
		}
		checkReady();
	});
    $("#submitButton").click(function(e) {
		$.ajax({
			type: "GET",
        	url: "http://magento.needle.com/generate.php?id=" +
        			$('#ruleSelector :selected').val() + "&name=" +
        			$('#ruleSelector :selected').text() + "&code=" +
        			$('#couponCode').val() + "&expire=" +
        			$('#expireDate').val(),
			dataType: "xml",
			success: function(xml) {
				$(xml).find('response').each(function(){
					var mStatus = $(this).attr('status');
					var mText = $(this).text();
					if (mStatus == "ERROR")
					{
						$('#completionDialog').dialog("option", "title", "Failed");
						$('#completionDialog').html(mText);
					}
					else
					{
						$('#completionDialog').dialog("option", "title", "Success");
						$('#completionDialog').html('Coupon is ready to use');
						resetForm();
					}
					$('#completionDialog').dialog('open');
				});
			}
		});
		return false;
	});
    $("#ruleSelector").change(function() {
        $('#selectedDescription').html($('#ruleSelector :selected').attr('title'));
		checkReady();
    });  
	$('#completionDialog').dialog({
		autoOpen: false,
		height: 125,
		modal: true,
		resizable: false,
		buttons: {
			'Close': function() {
				$(this).dialog('close');
			}
		}
	});
	// TODO: This is not currently shown - figure a way
	$('#selectedDescription').html($('#ruleSelector :selected').attr('title'));
});
</script>
</head>
<body>
<div id="containerDiv" class="ui-corner-all">
	<div id="accordion">
		<div>
			<h3><a href="#">Coupon Generator</a></h3>
				<form>
					<select id="ruleSelector" class="ui-corner-all">
						<option value="none" title="Select a coupon from the list and give it a unique code and an expiration date" selected>--- Select Discount Offer ---
						<option value="3333" title="This will give you an error">(Nonexistent)
						<option value="33" title="This will give you an error">(Already Exhausted)
<?php
$client = new SoapClient('http://184.73.176.25/magento/index.php/api/?wsdl', array("trace" => 1));
$session = $client->login('needle', '123456');
$filter = array(array('uses_per_coupon' => array('gt' => '0'), 'name' => array('like' => '_TEMPLATE:%')));
$result = $client->call($session, 'coupongenerator.list', $filter);
//print_r($result);
$count = count($result);
for ($i = 0; $i < $count; $i++) {
	echo "<option value=\"" 
		. $result[$i]['rule_id'] 
		. "\" title=\"" 
		. $result[$i]['description'] 
		. "\">" 
		. substr($result[$i]['name'], 10) 
		. "\n";
}
$client->endSession($session);
?>
					</select>
					<div id="selectedDescription" class="ui-corner-all"></div>
					<br>
				 	<input type="text" id="couponCode" value="Enter Unique Code for Customer" />
					<br>
					<input type="text" id="expireDate" />
					<select id="expireDays">
						<option value="0" selected>Today
						<option value="1">Tomorrow
						<option value="2">2 days
						<option value="3">3 days
						<option value="4">4 days
						<option value="5">5 days
						<option value="6">6 days
						<option value="7">7 days
					</select>
					<br>
					<input type="submit" id="submitButton" value="Generate Coupon" class="ui-priority-primary ui-corner-all">
				</form>
		</div>
		<div>
			<h3><a href="#">Comments about work...</a></h3>
				<ul>
					<li>Reads rules with names like '_TEMPLATE:%'
					<li>Shows matching rules in the list selector
					<li>Creates child coupons
					<li>Yes, a good UI person will find a lot to do here
					<li>TODO: Validate the form before submitting (the current red-box thingi is not to my liking)
					<li>I cannot seem to make a button behave: too difficult to go from disabled to hovering to not hovering. I give up.
				</ul>
		</div>
	</div>
	<div id="completionDialog"></div>
</div>
</body>
</html>
