<?php 
/*
Plugin Name: Fan Bank
Plugin URI: http://fanboy.dk
description: Fan Bank for members
Version: 1.0
Author: Benjamin Behrens
Author URI: http://benjaminbehrens.com
License: GPL2
*/


include_once('class/controller.class.php');
include_once('class/display.class.php');
include_once('class/recurring.class.php');
include_once('class/purchase.class.php');
include_once('class/shortcode.class.php');

$fanbank = new Fanbank();
$fb_display = new Display();
$fb_controller = new FB_Controller();
$fb_recurring = new FB_Recurring();
$fb_purchase = new FB_Purchase();
$fb_shortcodes = new FB_Shortcodes();


//check if memeber have that addon else dont give them currency
//check if 

//if user havnt used any of their currency or below 834 in the last 6 months add 834 or fewer dkk as standard
//if user have used less than 834 dkk, give them the rest (ex. user have used 300 dkk, they'll keep 534 dkk)

class Fanbank
{
  
  public $Title = 'FanBank';
  private $MaxAmountInFanBank = 3300;
  
  
  function __construct()
  {
    add_action( 'wp_enqueue_scripts', array($this , 'fanbank_style') );
    add_action('admin_head', array( $this ,'admin_register_head'));
  }
  

  
  
  protected function UserPaidEnough($userid)
  {
    if($this->getUserReceived($userid) >= $this->MaxAmountInFanBank){
      return true;
    }
    return false;
  }
  
  
  
  protected function needsToPay_frontend($userid)
  {
    $Calcualtion = $this->getUserPaid($userid) - (3300 - $this->getUsersPoints($userid));
    
    if($Calcualtion >= 0)
    {
      return '<p class="fanbank-need-to-pay">Kan frit bruge: <span class="fanbank-currency-positive">'. $Calcualtion .'</span> DKK</p>';
    }
    return '<p class="fanbank-need-to-pay">Mangler at betale tilbage: <span class="fanbank-currency-negative">'. abs($Calcualtion) .'</span> DKK</p>';
  }
  
  protected function needsToPay($userid)
  {
    $Calcualtion = $this->getUserPaid($userid) - (3300 - $this->getUsersPoints($userid));
    
    if($Calcualtion >= 0)
    {
      return '<b>Kan frit bruge:</b> <span style="color:#43d064; font-weight:bold;">'. $Calcualtion .'</span>';
    }
    return '<b>Mangler at betale:</b> <span style="color:#e82f2f; font-weight:bold;">'. abs($Calcualtion) .'</span>';
  }
  
  protected function needsToPay_Bool($userid)
  {
    $Calcualtion = $this->getUserPaid($userid) - (3300 - $this->getUsersPoints($userid));
    
    if($Calcualtion >= 0)
    {
      return false;
    }
    return true;
  }
  
  protected function needsToPay_Int($userid)
  {
    $Calcualtion = $this->getUserPaid($userid) - (3300 - $this->getUsersPoints($userid));
    
    if($Calcualtion >= 0)
    {
      return 0;
    }
    return abs($Calcualtion);
  }
  
  protected function getUsersPoints($userid)
  {//get_current_user_id()
    return get_user_meta($userid , 'FanBank')[0];
  }
  
  
  protected function getUserPaid($userid)
  {
    $paymentArray = array();
    $recordArray = json_decode(get_user_meta($userid , 'FanBank_record' )[0]);
    $spent = 0;
    
    if(isset($recordArray)){
      foreach($recordArray as $rec)
      {
        $spent += $rec->Paid;
      }
    }
    return $spent;
  }
  
  
  protected function getUserMonthsPaid($userid)
  {
    $paymentArray = array();
    $recordArray = json_decode(get_user_meta($userid , 'FanBank_record' )[0]);
    $months = 0;
    
    foreach($recordArray as $rec)
    {
      $months += $rec;
    }
    return $months;
  }
  
  
  protected function getUserReceived($userid)
  {
    $paymentArray = array();
    $recordArray = json_decode(get_user_meta($userid , 'FanBank_received' )[0]);
    $spent = 0;
    
    if($recordArray){
      foreach($recordArray as $rec)
      {
        $spent += $rec->Paid;
      }
    }
    return $spent;
  }
  
  
  protected function getUsersSubscribed()
  {
    $Users = array();
    
    // print_r(get_users());
    
    foreach(get_users() as $User){
      //print_r($User);
      if($this->checkIfFanBankMember($User->ID)){
        array_push($Users, $User->ID);
      }
    }
    
    return sizeof($Users);
    
  }
  
  
  protected function checkIfFanBankMember($userID)
  {
    if (wc_memberships_is_user_member($userID, 'fanbank-addon') ){
      return true;
    }
    return false;
  }
  
  
  function fanbank_style()
  {
      wp_register_style( 'fb-style', plugins_url( '/assets/css/fanbank_style.css', __FILE__ ), array(), '20120211', 'all' );
      wp_enqueue_style( 'fb-style' );
  }
  
  
  function admin_register_head() {
      $siteurl = get_option('home');
      $url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/assets/css/fanbank_style_admin.css';
      echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
  }
  
}
