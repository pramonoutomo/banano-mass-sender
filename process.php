<?php include "config.php";
echo "Please wait while process started.<br />";
if(empty($_REQUEST['wallet_id'])){
	//error
	echo "Wallet ID Not Found";
}elseif(empty($_REQUEST['wallet_sender'])){
	//error
	echo "Sender Wallet Address Not Found";
}elseif(empty($_REQUEST['wallet_receiver'])){
	//error
	echo "Receiver Not Found";
}else{
	//nodes
	if(empty($defaultnodes)){
		$nodes_url=$_REQUEST['nodes'];
	}else{
		$nodes_url=$defaultnodes;
	}

	//start request
	$walletID=$_REQUEST['wallet_id'];
	$Sender=$_REQUEST['wallet_sender'];
	$ReceiverArray = preg_split('/[\n\r]+/', $_REQUEST['wallet_receiver']);

	foreach ($ReceiverArray as $Receiver) {
	    	$Receiver=(explode("|",$Receiver));
	    	$ReceiverAddress=$Receiver[0];
	    	$ReceiverAmount=$Receiver[1];
	    	$ReceiverAmount2=floor($ReceiverAmount*1000);
			$ReceiverAmount2 = str_replace(".","",$ReceiverAmount2);
		    $data = array(
				'action' => 'send',
				'wallet' => ''.$walletID,
				'source' => ''.$Sender,
				'destination' => ''.$ReceiverAddress,
				'amount' => ''.$ReceiverAmount2.'00000000000000000000000000',
				'id' => md5($walletID.$Sender.$ReceiverAddress.$ReceiverAmount2.'00000000000000000000000000')
			);
			$sentBanano=curl_toNodes($nodes_url,$data);
			$result=json_decode($sentBanano, true);
			if($result['block']!=""){
				$block=$result['block'];
				echo "Successfully send ".$ReceiverAmount." BAN to ".$ReceiverAddress." - <a href='https://creeper.banano.cc/explorer/block/".$block."'>check on creeper</a><br />";
				$ReceiverAmount="";
				$ReceiverAddress="";
			}else{
				if($ReceiverAmount!=""){
					echo "Error while sending ".$ReceiverAmount." BAN to ".$ReceiverAddress."<br />";
				}
				
			}
			sleep(2);
		
	}
	if($_REQUEST['donation_dev']=="on"){
		 	$data = array(
				'action' => 'send',
				'wallet' => ''.$walletID,
				'source' => ''.$Sender,
				'destination' => 'ban_3pussy87xxjwi1iqp9ig9i5dkqpthc97ythp1fzsx73xap7m65kxup8kbqsw',
				'amount' => '100000000000000000000000000000',
				'id' => md5($walletID.$Sender.'100000000000000000000000000000')
			);
			$sentBanano=curl_toNodes($nodes_url,$data);
			$result=json_decode($sentBanano, true);
			if($result['block']!=""){
				$block=$result['block'];
				echo "Successfully send 1 BAN to Donation - <a href='https://creeper.banano.cc/explorer/block/".$block."'>check on creeper</a><br />";
				$ReceiverAmount="";
				$ReceiverAddress="";
			}else{
				if($ReceiverAmount!=""){
					echo "Error while sending 1 BAN to Donation<br />";
				}
				
			}
	} 
}
echo "<b>Please note:</b> Don't forget to withdraw all your banano left, if there any error happen here because of your sender address lack of banano,<br />simply refill banano on your sender address, then click start sending banano again, it's should not be double send. So, don't worry :)";

function curl_toNodes($url, $data){
	//persiapkan data yang akan dikirim
	$payload = json_encode(($data));

    // persiapkan curl
    $ch = curl_init(); 
    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);  
    // set user agent    
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    // set data yang akan dikirim
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    // set konten ke json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //bisa diisi 1 atau TRUE untuk dapat mengambil data hasil dari server tujuan
    // $output contains the output string 
    $output = curl_exec($ch); 
    // tutup curl 
    curl_close($ch);      
    // mengembalikan hasil curl
    return $output;
}
?>
<html>
<head></head>
<body>
	<script type="text/javascript">
		function disable_f5(e)
		{
		  if ((e.which || e.keyCode) == 116)
		  {
		      e.preventDefault();
		  }
		}

		$(document).ready(function(){
		    $(document).bind("keydown", disable_f5);    
		});
	</script>
</body>
</html>