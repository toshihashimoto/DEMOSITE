<?php

session_start();
session_destroy();
$MODE="old";
if(!file_exists("stn/new_assets/test.png")) {
	$MODE="new";
}
$error=false;

if(!file_exists("stn/maintenance.txt")) {
	
	if($MODE=="old") {
	
	
	
																				if(isset($_SESSION['LOGGED_IN']) && $_SESSION['LOGGED_IN']===true) {
																				
																					header("Location: stn/home.php");
																				}
																				
																				
																				if(isset($_POST['agency_id'])) {
																				
																				
																					$em="";
																					foreach($_POST as $field=>$field_value) {
																					
																						$em.="$field = $field_value\n";
																					}
																					
																					//mail("alandelima@colorgraphics.com","Posted Form","They tested this. \n Values are \n$em");
																					
																					
																				
																					include_once("stn/inc.myriaddb.php");
																					include_once("stn/inc.functions.php");
																					
																					$agency_id=intval($_POST['agency_id']);
																					$pmv=trim($_POST['pmv']);
																					$issue_code_entered=$_POST['issue_code'];
																					
																					//Check if valid agency to login
																					if($agency_id!=="" && $pmv!=="") {
																						$q="SELECT * FROM agencies WHERE agency_id=".get_sql_value($agency_id,"int")." AND pmv=".get_sql_value($pmv,"text")." ORDER BY agency_id LIMIT 1";
																						
																						$res=mysql_query($q) or die(mysql_error());
																						
																						if(mysql_num_rows($res)) {
																							
																							$rs=mysql_fetch_assoc($res);
																							
																							$_SESSION['LOGGED_IN']=true;
																							
																							$_SESSION['HAS_LOGOS']=count(glob("stn/assets/logos/$pmv*.*"))?"yes":"no";
																							
																							//save agencies
																							$_SESSION['TABLES']['agencies']=$rs;
																							$_SESSION['TABLES']['agencies']['agency_name']=$_SESSION['TABLES']['agencies']['agency_name'];
																							if(isset($_POST['logged_in_from']) && $_POST['logged_in_from']=="colorgraphics") {
																							
																								$_SESSION['TABLES']['agencies']['logged_in_from']="colorgraphics";
																							}
																							else {
																							
																								$_SESSION['TABLES']['agencies']['logged_in_from']="signature";
																							}
																							
																							//Get all Active issue_codes
																							$res_issue_codes=mysql_query("SELECT issue_code, display_order FROM issues where issue_status = 'active' ORDER BY display_order") or die(mysql_error());
																							
																							
																							//for($c=0; $c<3; $c++) {
																							if(mysql_num_rows($res_issue_codes)) {
																							
																							$_SESSION['VIEWED']=array();
																							$c=0;
																								
																										
																							while($rs_issue_codes=mysql_fetch_assoc($res_issue_codes)) {
																								
																								$issue_code=$rs_issue_codes['issue_code'];
																								
																								if($issue_code_entered==$issue_code) {
																									
																									$_SESSION['PUBLICATION_INDEX']=$c;
																									if($c==0) {
																										$_SESSION['VIEWED'][0]=true;
																									}
																									else {
																										$_SESSION['VIEWED'][$c]=false;
																									}
																								}
																								
																								//get issues
																								$q="SELECT * FROM issues WHERE issue_status='active' AND issue_code='$issue_code'";
																								$res=mysql_query($q) or die(mysql_error());
																								
																								while ($rs=mysql_fetch_assoc($res)) {
																								
																									$_SESSION['TABLES']['issues'][$c]=$rs;
																									
																									$issue_id=$rs['issue_id'];
																									//check if there is a corresponding agency_issues
																									$q="SELECT * FROM agency_issues WHERE agency_id=".get_sql_value($agency_id,"int")." AND pmv=".get_sql_value($pmv,"text").
																										" AND issue_id=$issue_id ";
																									$res2=mysql_query($q) or die(mysql_error());
																									if(mysql_num_rows($res2)==0) {
																										
																										$log="Initiated issue on ".date("m/d/y H:i")."\n";
																										//create agency_issue
																										$q="INSERT INTO agency_issues (agency_id,pmv,issue_id,log) VALUES ($agency_id,'$pmv',$issue_id,'$log')";
																										$res3=mysql_query($q) or die(mysql_error());
																									
																									}
																									
																									//check if there is a corresponding agency_issues
																									$q="SELECT * FROM agency_issues WHERE agency_id=".get_sql_value($agency_id,"int")." AND pmv=".get_sql_value($pmv,"text").
																										" AND issue_id=$issue_id ";
																									$res2=mysql_query($q) or die(mysql_error());
																									$rs2=mysql_fetch_assoc($res2);
																									$_SESSION['TABLES']['agency_issues'][$c]=$rs2;
																									
																									//get issue_pages
																									$q="SELECT * FROM agency_issue_pages WHERE agency_id=".get_sql_value($agency_id,"int")." AND pmv=".get_sql_value($pmv,"text")." AND issue_id=$issue_id ";;
																									$res2=mysql_query($q) or die(mysql_error());
																									if(mysql_num_rows($res2)==0) {
																									
																										$q="SELECT * FROM issue_pages WHERE issue_id=$issue_id ORDER BY spread_id, spread_side";
																										$res3=mysql_query($q) or die(mysql_error());
																										
																										//create agency_issue_pages
																										while($rs3=mysql_fetch_assoc($res3)) {
																											
																											$issue_page_id=$rs3['issue_page_id'];
																											$q="INSERT INTO agency_issue_pages (issue_page_id,agency_id,pmv,issue_id) VALUES ($issue_page_id,$agency_id,'$pmv',$issue_id)";
																											$res4=mysql_query($q) or die(mysql_error());
																										
																										}
																									}
																									
																														
																									//store issue_pages
																									$q="SELECT * FROM agency_issue_pages WHERE agency_id=".get_sql_value($agency_id,"int")." AND pmv=".get_sql_value($pmv,"text")." AND issue_id=$issue_id ";;
																									$res2=mysql_query($q) or die(mysql_error());	
																									$i=0;
																									while($rs2=mysql_fetch_assoc($res2)) {
																									
																										$_SESSION['TABLES']['agency_issue_pages'][$c][$i++]=$rs2;
																									}
																				
																									
																									
																									$q="SELECT * FROM issue_pages WHERE issue_id=$issue_id ORDER BY spread_id, spread_side";
																									$res2=mysql_query($q) or die(mysql_error());
																									
																									$i=0;
																									while($rs2=mysql_fetch_assoc($res2)) {
																									
																										$_SESSION['TABLES']['issue_pages'][$c][$i++]=$rs2;
																									}
																								}
																								
																								//ADDED 11/17/09
																								$side_text="<side_text><![CDATA[".$_SESSION['TABLES']['issues'][$c]['side_text']."]]></side_text>
																								";
																								//END ADD
																								
																								
																								
																								//EMAIL_CHANGES XML
																								$_SESSION['XML'][$c]['email_changes']='<email_changes>
																								<agency_id>'.$_SESSION['TABLES']['agencies']['agency_id'].'</agency_id>
																								<pmv>'.$_SESSION['TABLES']['agencies']['pmv'].'</pmv>
																								<agency_name><![CDATA['.$_SESSION['TABLES']['agencies']['agency_name'].']]></agency_name>';
																								
																								if($_SESSION['TABLES']['issues'][$c]['has_custom_letter']=="yes") {
																									$_SESSION['XML'][$c]['email_changes'].='<show_upload>1</show_upload>';
																								}
																								else {
																									$_SESSION['XML'][$c]['email_changes'].='<show_upload>0</show_upload>';
																								}
																								$_SESSION['XML'][$c]['email_changes'].=$side_text.'
																								</email_changes>
																								';
																								
																								//PUBLICATION DESCRIPTION XML
																								$_SESSION['XML'][$c]['publication_description']='<publication_description><![CDATA[
																								'.nl2br($_SESSION['TABLES']['issues'][$c]['issue_description']).'
																								]]></publication_description>
																								';
																								
																								
																								
																								//EMAIL_CHANGES XML
																								$_SESSION['XML'][$c]['email_only']='<email_only>
																								<agency_id>'.$_SESSION['TABLES']['agencies']['agency_id'].'</agency_id>
																								<pmv>'.$_SESSION['TABLES']['agencies']['pmv'].'</pmv>
																								<agency_name><![CDATA['.$_SESSION['TABLES']['agencies']['agency_name'].']]></agency_name>';
																								
																								if($_SESSION['TABLES']['issues'][$c]['has_custom_letter']=="yes") {
																									$_SESSION['XML'][$c]['email_only'].='<show_upload>1</show_upload>';
																								}
																								else {
																									$_SESSION['XML'][$c]['email_only'].='<show_upload>0</show_upload>';
																								}
																								$_SESSION['XML'][$c]['email_only'].=$side_text.'
																								</email_only>
																								';
																								
																								
																								
																								//PUBLICATION DESCRIPTION XML
																								$_SESSION['XML'][$c]['publication_description']='<publication_description><![CDATA[
																								'.nl2br($_SESSION['TABLES']['issues'][$c]['issue_description']).'
																								]]></publication_description>
																								';
																								
																								
																								
																								
																								//PUBLICATION XML
																								$_SESSION['XML'][$c]['publication']='<publication name="'.$_SESSION['TABLES']['issues'][$c]['issue_title'].
																									'" icon="'.$_SESSION['TABLES']['issues'][$c]['issue_icon'].
																									'" has_custom_letter="'.$_SESSION['TABLES']['issues'][$c]['has_custom_letter'].
																									'" cutoff_date="'.$_SESSION['TABLES']['issues'][$c]['cutoff_date'].
																									'" issue_date="'.$_SESSION['TABLES']['issues'][$c]['issue_date'].
																									'" issue_code="'.$_SESSION['TABLES']['issues'][$c]['issue_code'].
																									'" is_approved="'.$_SESSION['TABLES']['agency_issues'][$c]['is_approved'].
																									'" approved_date="'.$_SESSION['TABLES']['agency_issues'][$c]['approved_date'].
																									'" is_rejected="'.$_SESSION['TABLES']['agency_issues'][$c]['is_rejected'].
																									'" rejected_date="'.$_SESSION['TABLES']['agency_issues'][$c]['rejected_date'].
																									'" rejected_revision="'.$_SESSION['TABLES']['agency_issues'][$c]['rejected_revision'].
																									'" revision="'.$_SESSION['TABLES']['agency_issues'][$c]['revision'].
																									'" w1="'.$_SESSION['TABLES']['issues'][$c]['w1'].
																									'" h1="'.$_SESSION['TABLES']['issues'][$c]['h1'].
																									'" w2="'.$_SESSION['TABLES']['issues'][$c]['w2'].
																									'" h2="'.$_SESSION['TABLES']['issues'][$c]['h2'].
																									'" w3="'.$_SESSION['TABLES']['issues'][$c]['w3'].
																									'" h3="'.$_SESSION['TABLES']['issues'][$c]['h3'].
																									'" has_logos="'.$_SESSION['HAS_LOGOS'].'" ';
																								
																								$pic_temp=$_SESSION['TABLES']['issues'][$c]['issue_code']."_".$_SESSION['TABLES']['agencies']['pmv'];
																								$issue_pages_temp="";
																								$has_src=true;
																								
																								foreach($_SESSION['TABLES']['issue_pages'][$c] as $ip) {
																								
																									$f_dir="stn/assets/".$_SESSION['TABLES']['issues'][$c]['issue_code']."/".$_SESSION['TABLES']['issues'][$c]['issue_date']."/".$_SESSION['TABLES']['issues'][$c]['w1'].
																										"x".$_SESSION['TABLES']['issues'][$c]['h1']."/";
																									
																									$src=$pic_temp."_".$ip['spread_id']."_".$ip['spread_side'].".jpg";
																									
																									if(!is_file($f_dir.$src)) {
																										
																										$src="none";
																										$has_src=$has_src && false;
																									}
																														
																									$issue_pages_temp.='
																									<issue_pages spread_id="'.$ip['spread_id'].
																									'" spread_side="'.$ip['spread_side'].
																									'" src="'.$src.
																									'" viewed="Not yet" >'.$ip['issue_page_description'].'</issue_pages>
																									';
																								}
																								
																								$src_text="";
																								if($has_src) {
																									
																									$src_text=' has_preview="yes" ';
																								}
																								else {
																									
																									$src_text=' has_preview="no" ';
																								}
																								
																								$_SESSION['XML'][$c]['publication'].=$src_text.' >
																									'.$issue_pages_temp.'
																									'.$side_text.'
																									</publication>
																									';
																								
																								
																								//Enter to logs
																								$log_text="Logged in from ".strtoupper($_SESSION['TABLES']['agencies']['logged_in_from'])."\nDate: ".date("F j, Y g:i A")."\nIP: ".$_SERVER['REMOTE_ADDR'];
																								$log_text.="\nPublication: $issue_code_entered";
																								$log_text.="\nUser Agent: ".$_SERVER['HTTP_USER_AGENT'];
																								$q="INSERT INTO logs (agency_id,pmv,log_type,activity) VALUES ($agency_id,'$pmv','Login',".get_sql_value($log_text,"text").")";
																								$res=mysql_query($q);
																								
																								//Check if new revision
																								$new_revision=' new_revision="original" ';
																								if(intval($_SESSION['TABLES']['agency_issues'][$c]['is_rejected'])) {
																								
																									if($_SESSION['TABLES']['agency_issues'][$c]['rejected_revision']!=$_SESSION['TABLES']['agency_issues'][$c]['revision']) {
																									
																										$new_revision=' new_revision="yes" ';
																									}
																									else {
																										$new_revision=' new_revision="no" ';
																									}
																									
																								}
																								
																								$_SESSION['XML'][$c]['show_buttons']='<show_buttons '.$new_revision.
																									'has_customization="'.$_SESSION['TABLES']['issues'][$c]['has_custom_letter'].'" '.$src_text.
																									' ></show_buttons>';
																								
																								$c++;
																							}
																							$_SESSION['NUMBER_OF_ACTIVE_PUBLICATIONS']=$c;
																							//End for loop of while loop
																							
																							
																							header("Location: stn/home.php");
																							
																							}
																							//End if number of active issue_codes > 0
																							else {
																								
																								$error=true;
																							}
																							
																						}
																						else {
																						
																							$error=true;
																						}
																					}
																					else {
																						
																						$error=true;
																					}
																				}
																				else {
																				
																					$error=true;
																				}
	}
	else 		{
		
		$error=true;	
		if(isset($_POST['agency_id'])) {
			
			include_once("stn/api/api.functions.php");
			
			$agency_id=intval($_POST['agency_id']);
			$pmv=trim($_POST['pmv']);
			if(strlen($pmv)==3) {
				$pmv="0$pmv";
			}
			$issue_code_entered=$_POST['issue_code'];
			
			
			logToActivity($pmv,$agency_id,"Login Attempt","IP: ".$_SERVER['REMOTE_ADDR']);
			try {
				$sql = "SELECT * FROM agencies WHERE agency_id=:agency_id AND pmv=:pmv";
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->bindParam("agency_id", $agency_id);
				$stmt->bindParam("pmv",$pmv );
				$stmt->execute();
				
				if($stmt->rowCount()) {
					$_SESSION[AGENCY_INFO] = $stmt->fetch(PDO::FETCH_ASSOC);
					$_SESSION[AGENCY_LOGGED_IN]=true;
					$db=null;
					sleep(1);
					logToActivity($pmv,$agency_id,"Logged In","IP: ".$_SERVER['REMOTE_ADDR']);
					updateSession();
					
					header("Location: https://www.colorgraphics.com/stn/app_home.php#dashboard");
				}
				else {
					session_destroy();
					$error=true;	
				}
				$db = null;
			} catch(PDOException $e) {
				$error=true;
			}		
		
		}
	}
		
		
		
		
	if($error) {
	
		if($agency_id && $pmv && $issue_code) {
		
			$b="PMV Entered = $pmv\nAgency ID Entered = $agency_id\nMagazine Code Entered = $issue_code_entered\nDate of Login = ".date("m/d/y H:i")."\n".
			"IP = ".$_SERVER['REMOTE_ADDR']."\n\n\n";
			mail("alandelima@colorgraphics.com","INVALID LOGIN CAPTURED FOR SIGNATURE APRROVALS",$b);
			mail("approvals@myriadmarketing.com","INVALID LOGIN CAPTURED FOR SIGNATURE APRROVALS",$b);
		}
	
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Signature Travel Approvals</title>
	<link href="stn/stn.css" rel="stylesheet" type="text/css" />
	</head>
	<body onload="document.login_form.agency_id.focus()">
	<div id="login_div">
	<img src="stn/images/signature_logo.jpg" style="margin-bottom:20px;" />
	<table cellpadding="2" cellspacing="2" style="text-align:left; width:300px; margin:0 auto;">
	<tr>
	<td><div style="color:#990000; text-align:center"><b>Not a valid Login.<br />
	Please contact Signature Travel Network!</b><br />
	&nbsp;<br />
	<a href="http://www.signaturetravelnetwork.com/Marketing/approvals_processed.cfm">Click here to go Back</a>
	
	</div></td>
	</tr>   
	</table>  
	</div>
	</body>
	</html>
	<?php
	}																		
																		
																				
}
else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Signature Travel Approvals</title>
<link href="stn/stn.css" rel="stylesheet" type="text/css" />
</head>
<body onload="document.login_form.agency_id.focus()">
<div id="login_div">
<img src="stn/images/signature_logo.jpg" style="margin-bottom:20px;" />
<table cellpadding="2" cellspacing="2" style="text-align:left; width:300px; margin:0 auto;">
<tr>
    <td><div style="color:#990000; text-align:center">
   <!-- <strong>Temporarily Down for Maintenance</strong> -->
		<p>Please await further instructions to review and submit your customization changes on or around July 9, 2018.<br>
For any further questions please contact <br><a href="mailto:marketing@signaturetravelnetwork.com">marketing@signaturetravelnetwork.com</a>.</p>
		<!--
    <p>We are performing scheduled maintenance.<br />
    We will be back online within 24 hours.<br />
    Thank you for your cooperation.</p>
	-->
</div></td>
</tr>   
</table>  
</div>
</body>
</html>
<?php
}
?>