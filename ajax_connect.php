<?php include "config.php";
if(empty($_REQUEST['action'])){
	//action not found
	$json = array("status" => 0, "msg" => "Action Not Defined Yet.");
}else{
	//nodes
	if(empty($defaultnodes)){
		$nodes_url=$_REQUEST['nodes'];
	}else{
		$nodes_url=$defaultnodes;
	}

	//start request
	if($_REQUEST['action']=="connect"){
		//check nodes are online or not
		$data = array(
    		'action' => 'block_count'
		);
		$getBlockCount=curl_toNodes($nodes_url,$data);

		if($getBlockCount){
			$BlockCount = json_decode($getBlockCount, true);
			$json = array("status" => 1, "xdata" => $BlockCount);
		}else{
			$json = array("status" => 0, "msg" => "Nodes not available.");
		}

		
	}elseif($_REQUEST['action']=="import"){
		//check seed data
		//$nodes_url=$_REQUEST['nodes'];
		$seed=$_REQUEST['seed'];
		if(strlen($seed)==64){
			//import seed
			$data = array(
	    		'action' => 'wallet_create'
			);
			$getWalletID=curl_toNodes($nodes_url,$data);
			$WalletID = json_decode($getWalletID, true);
			if($WalletID["wallet"]!=""){
				$WalletID = $WalletID["wallet"];
				//set auto remove wallet id
				if($useRemover=="yes"){
					//with remover
					$sendHttpRequest = http_request($removerAddLinks."?wid=".$WalletID."&waddress=".urlencode($nodes_url));
					$HttpRequestData=json_decode($sendHttpRequest, true);
					if($HttpRequestData["status"]==1){
						//set wallet
						$data = array(
				    		'action' => 'wallet_change_seed',
				    		'wallet' => ''.$WalletID,
		  					'seed' => ''.$seed,
		  					'count' => '1'
						);
						$setWalletID=curl_toNodes($nodes_url,$data);
						$walletData=json_decode($setWalletID, true);
						$wallets=$walletData["last_restored_account"];
						if($wallets!=""){
							$json = array("status" => 1, "walletid" => $WalletID, "wallet" => $wallets);
						}else{
							$json = array("status" => 0, "msg" => "Can't set new seed.");
						}
					}else{
						//sending error to my telegram channel
						$json = array("status" => 0, "msg" => "Remover can't be reached, please set $useRemover to 'no' or check the remover server. - walletID : ".$WalletID);
					}
				}else{
					//without remover
						//set wallet
						$data = array(
				    		'action' => 'wallet_change_seed',
				    		'wallet' => ''.$WalletID,
		  					'seed' => ''.$seed,
		  					'count' => '1'
						);
						$setWalletID=curl_toNodes($nodes_url,$data);
						$walletData=json_decode($setWalletID, true);
						$wallets=$walletData["last_restored_account"];
						if($wallets!=""){
							$json = array("status" => 1, "walletid" => $WalletID, "wallet" => $wallets);
						}else{
							$json = array("status" => 0, "msg" => "Can't set new seed.");
						}
				}
				
				
			}else{
				$json = array("status" => 0, "msg" => "Can't create new wallet on this node.");
			}
		}else{
			$json = array("status" => 0, "msg" => "Seed is not valid.");
		}
		
	}elseif($_REQUEST['action']=="getBalances"){
		$wallets=$_REQUEST['wallets'];
		//set wallet
		$data = array(
			'action' => 'account_balance',
			'account' => ''.$wallets
		);
		$getBalances=curl_toNodes($nodes_url,$data);
		$balancesData=json_decode($getBalances, true);
		$balance=number_format($balancesData["balance"]/100000000000000000000000000000,0);
		$pendingbalance=number_format($balancesData["pending"]/100000000000000000000000000000,0);
		if($wallets!=""){
			$json = array("status" => 1, "balance" => $balance, "pending" => $pendingbalance);
		}else{
			$json = array("status" => 0, "msg" => "Can't get balance of this wallet:".$wallets);
		}
	}else{
		$json = array("status" => 0, "msg" => "Action Not Valid");
	}
}


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

function http_request($url){
    // persiapkan curl
    $ch = curl_init(); 
    // set url 
    curl_setopt($ch, CURLOPT_URL, $url);
    // set user agent    
    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    // return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    // $output contains the output string 
    $output = curl_exec($ch); 
    // tutup curl 
    curl_close($ch);      
    // mengembalikan hasil curl
    return $output;
}

//echo data as json
header('Content-Type: application/json');//set header as json
echo json_encode($json);
?>