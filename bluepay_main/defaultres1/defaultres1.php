<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Displays Return Receipt to user and also sends payment user data to the trans_notify.php file to allow or deny class entry
 *
 * @package    enrol
 * @subpackage bluepay
 * @copyright  2013 Mark V Madsen www.starportmedia.com Starport Media and Software
 * @author     www.starportmedia.com - Original code by Mark V Madsen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


// aquires the Admin set dynamic url from the bluepay module database for cUrl to send data to the trans_notify.php file on the owners server
require("../../../config.php");
require_once($CFG->libdir.'/enrollib.php');
$plugin = enrol_get_plugin('bluepay');
$module_url = $plugin->get_config('moduleurl');
$my_url = $module_url . '/trans_notify.php';

$amex_image = $_GET["AMEX_IMAGE"];
$dba = $_GET["DBA"];
$amount = $_GET["AMOUNT"];
$rrno = $_GET["RRNO"];
$card_type = $_GET["CARD_TYPE"];
$payment_account = $_GET["PAYMENT_ACCOUNT"];
$result = $_GET["Result"];
$issue_date = $_GET["ISSUE_DATE"];
$message = $_GET["MESSAGE"];
$return_url = $_GET["RETURN_URL"];
$cid = $_GET["CUSTOM_ID"];
$cid2 = $_GET["CUSTOM_ID2"];
$invoiceid = $_GET["INVOICE_ID"];
$bpstamp = $_GET["BP_STAMP"];
$trans_type = $_GET["TRANS_TYPE"];
$trans_id = $_GET["TRANS_ID"];
$trans_status = $_GET["STATUS"];
$payment_type = $_GET["PAYMENT_TYPE"];

?>

<html>
  <head>
    <link rel="stylesheet" type="text/css" media="screen" href="css/style3.css" />
    <script type="text/javascript">
      window.onload = function() {
      /* set your parameters(number to countdown from, pause between counts in milliseconds, function to execute when finished)*/
      startCountDown(60, 1000, myFunction);
      }

      function startCountDown(i, p, f) {
      // store parameters
      var pause = p;
      var fn = f;
      // make reference to div
      var countDownObj = document.getElementById("countDown");
      if (countDownObj == null) {
	// error
	//alert("div not found, check your id");
	// bail
	return;
      }
      countDownObj.count = function(i) {
      // write out count
      countDownObj.innerHTML = i;
      if (i == 0) {
	// execute function
	fn();
	// stop
	return;
      }
      setTimeout(function() {
      // repeat
	countDownObj.count(i - 1);
      },
      pause
      );
      }
      // set it going
      countDownObj.count(i);
      }
      function myFunction() {
	document.getElementById('thelink').style.visibility = "visible";
      //document.write("<a href='<?php echo filter_var($return_url, FILTER_SANITIZE_STRING); ?>'><font size='+1'><b>Click here to enter your class</b></font></a>");
      }
</script>
    <?php
    function cef_message($message) {
	  switch($message) {
	      case "Unable to locate account":
		return "We werenÕt able to locate your bank or credit card account. It could be closed. Please check your payment information.";
		break;
	      case "SECURITY ERROR":
		return "There is an authentication error. This is a system error. Please contact the help desk and include the text of this error. ";
		break;
	      case "TRANSACTION IS TOO OLD":
		return "Your transaction is too old. ";
		break;
	      case "DD_ROUTING and DD_ACCOUNT required":
		return "European Direct Debit Routing and Direct Debit Account are required. Please supply these details.";
		break;
	      case "ACH_ROUTING and ACH_ACCOUNT required":
		return "Your routing number and bank account number are required.";
		break;
	      case "INVALID KSN":
		return "There was bad encryption data. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "INVALID DUKPT":
		return "There was bad encryption data. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "INVALID TRACK DATA":
		return "The swiped credit card data is not correct.";
		break;
	      case "AUTOCAP NOT VALID UNLESS AUTH":
		return "The system ran a transaction with AUTOCAP (deprecated option) that wasn't an AUTH. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "There was an unspecified error":
		return "There was an general error. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Invalid transaction type.":
		return "The transaction type was not valid. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Invalid transaction type.":
		return "The transaction type was not valid. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Master ID required for CAPTURE":
		return "A Master ID is required for CAPTURE transactions. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Expiration date required for CREDIT":
		return "Please supply an expiration date.";
		break;
	      case "Missing PIN data":
		return "No debit PIN was supplied.";
		break;
	      case "DEBIT only supported for SALE and REFUND or CREDIT":
		return "For these transactions, only the debit mode can be used. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "DEBIT require Track 2 (Swipe) Data":
		return "The debit transaction requires Track 2 (Swipe) data. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Cash back only valid on DEBIT.":
		return "You can only get cash back on a debit transaction.";
		break;
	      case "Invalid CVV2":
		return "The CVV information entered is incorrect.";
		break;
	      case "Unable to locate account":
		return "We werenÕt able to locate your bank or credit card account. It could be closed. Please check your payment information.";
		break;
	      case "Unable to locate user":
		return "Unable to locate user";
		break;
	      case "MISSING PAYMENT ACCOUNT":
		return "No credit card number was entered.";
		break;
	      case "PAYMENT ACCOUNT INFO ERROR":
		return "There was an issue looking up your bank or credit carrd account. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "ACH not eligible for Aggregation":
		return "There was an issue with the ACH aggregation system. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "CARD ACCOUNT NOT VALID":
		return "The credit card number does not appear to be valid. Please check the number.";
		break;
	      case "ENCRYPTION ERROR":
		return "We were unable to encrypt the credit card data. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Error reading master transaction":
		return "The system was unable to locate tokenized transaction. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "REFUND may only be performed on original card":
		return "We can only issue a refund back to the original card.";
		break;
	      case "Already Captured":
		return "This card transaction has already been captured. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Only AUTH can be CAPTURED":
		return "The system attempted to capture a zero-dollar AUTH transaction. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Cannot CAPTURE Zero AUTH":
		return "The system attempted to capture a zero-dollar AUTH. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "This AUTH is VOID":
		return "The system attempted to capture a voided AUTH transaction. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Need voice auth code to CAPTURE this AUTH":
		return "The system attempted to capture a declined AUTH transaction. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Already Voided":
		return "The system attempt to void a transaction that is already voided. This is a system error. Please contact the help desk and include the text of this error.";
		break;
	      case "Cannot VOID except for full amount.":
		return "The system cannot void a partial amount.";
		break;
	      case "Refund/VOID amount cannot exceed original charge amount":
		return "An attempt to refund or void more than the original transaction amount was made. This is not allowed. Please contact the help desk for more information.";
		break;
	      case "Can only REFUND or VOID approved transactions":
		return "An attempt to void a declind transaction was made. This is not allowed. Please contact the help desk for more information.";
		break;
	      case "Cannot VOID a CAPTURED AUTH (void the CAPTURE instead).":
		return "An attempt to void a captured auth transaction was made. This is not allowed. Please contact the help desk for more information.";
		break;
	      case "VOID cannot be undone":
		return "An attempt to void a voided transaction was made. This is not allowed. Please contact the help desk for more information.";
		break;
	      case "CREDIT Rebillings only supported on ACH":
		return "Credit rebilling actions are only possible with ACH transactions. This is a system error. Please contact the help desk with the text of this error.";
		break;
	      case "ROUTING NUMBER NOT VALID":
		return "The routing number supplied is not valid. Please check it and try again.";
		break;
	      case "Amount may not be zero.":
		return "Zero-dollar amounts are not allowed. This is a system error. Please contact the help desk with the text of this error.";
		break;
	      case "ERROR SELECTING PROCESSOR":
		return "BluePay couldn't select a processor. This is a system error. Please contact the help desk with the text of this error.";
		break;
	      default:
		return "";
		break;
	  }
      }
    ?>
  </head>
	<body>
            <center>
	      <table bgcolor="#ac003e" style="height: 5px; width: 100%;">
		<tr>
		    <td bgcolor="#ac003e">
		    </td>
		</tr>
	    </table>
	    <table bgcolor="#F4F4F4" width="100%" style="border-bottom: 1px solid #E8E8E8;">
		<tr>
		    <td bgcolor="#F4F4F4">
			<img width="100%" style="margin-bottom: 5px;" border="0" alt="" src="images/CEF_ELearning.jpg">
		    </td>
		</tr>
	    </table>
		<form name="mainform" class="mainform" action="" method="POST">
			<center>
	    <a href="http://www.bluepay.com" target="_new"><img src="images/BluePay.jpg" border="0"></a><br>&nbsp;</center>
			<fieldset>
				<legend>Payment Results</legend>
				<ol>
				<li><center><img src="images/visa.gif"><img src="images/mc.gif"><img src="images/<?php echo $_GET["DISCOVER_IMAGE"]; ?>"><img src="images/<?php echo filter_var($amex_image, FILTER_SANITIZE_STRING); ?>"></center></li>
				<li>&nbsp;<br><label for="DBA">Merchant</label><?php echo filter_var($dba, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="AMOUNT">Amount</label>$ <?php echo filter_var($amount, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="RRNO">Transaction ID</label><?php echo filter_var($rrno, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="Result">Credit Card Type</label><?php echo filter_var($card_type, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="Result">Credit Card Number</label><?php echo filter_var($payment_account, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="Result">Result</label><?php echo filter_var($result, FILTER_SANITIZE_STRING); ?></li>
				<li><label for="Message">Message</label><?php echo cef_message(filter_var($message, FILTER_SANITIZE_STRING)); ?></li>
				</ol>
			</fieldset>
		</form>
                <p align="center">
		  <?php
		  if ($result == 'APPROVED') {
		    
		      /*$post_vars = array(
			'custom_id1' => $cid,
			'custom_id2' => $cid2,
			'invoice_id' => $invoiceid,
			'amount' => $amount,
			'payment_type' => $payment_type,
			'BP_STAMP' => $bpstamp,
			'trans_status' => $trans_status,
			'trans_type' => $trans_type,
			'trans_id' => $trans_id,
			'rrno' => $rrno,
			'result' => $result,
			'issue_date' => $issue_date,
		      );*/
		      
		      $post_vars = 'custom_id1='. $cid .'&custom_id2='. $cid2 .'&invoice_id='. $invoiceid .'&amount='. $amount .'&payment_type='. $payment_type .'&BP_STAMP='. $bpstamp .'&trans_status='. $trans_status .'&trans_type='. $trans_type 
		      .'&trans_id='. $trans_id .'&rrno='. $rrno .'&result='. $result .'&issue_date='. $issue_date;		
  
		      $curl = curl_init($my_url);
		      curl_setopt($curl, CURLOPT_POST, 1);
		      //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_vars)); // this needs possibly the correct encoding switch to work. It only outputs first point of array when config.php is in code...??
		      curl_setopt($curl, CURLOPT_POSTFIELDS, $post_vars);
		      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		      curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		      $response = curl_exec($curl);
		      
		      // debugging code
		      //$lastUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
		      //$info = curl_getinfo($curl);
		      //echo $info['request_header'] . '<br>';	      
		      //echo $my_url . '<br>';
		      //echo $lastUrl . '<br>';
		      //echo $response. '<br>';
		      
		      curl_close($curl);

		    //echo '<font size="+1"><b>* Preparing your class. Please wait <span style="color: red;" id="countDown"></span> seconds for your link to appear.</b></font><br>';
		    echo '<font size="+1"><a href="';
		    echo filter_var($return_url, FILTER_SANITIZE_STRING);
		    echo '"><b><span id="thelink" style="visibility:visible;">Click here to enter your class</span></b></font></a>';
		  } else {
		    echo '<font size="+1"><b>* There was a problem with your submission. Please use this link to correct your information.</b></font><br></font>';
		    echo '<font size="+1"><a href="/moodle/">Click to return to Course Payment</a></font>';
		  }
		  ?>
		</p>
            </center>
	</body>
</html>