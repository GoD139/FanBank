<?php 




/**
 * 
 */
class FB_Recurring extends Fanbank
{
  
  private $oneMonthFromNow;
  private $userPaysMonthly = 139;
  private $userIsPaidMonthly = 550;
  
  function __construct()
  {
    $this->oneMonthFromNow = date("Y-m-d H:i:s", strtotime("+1 month"));

    add_action('user_register', array($this, 'executeOnUserRegistration'));
    
    //check if a user should get their recurring monthly bonus
    add_action( 'init', array($this, 'RecurrenceChecker') );
  }
  
  function executeOnUserRegistration($user_id){
    $this->createDateToRecurre($userID);
  }
  
  public function RecurrenceChecker()
  {
    
    // echo '<pre>';
    // //print_r(get_users());
    // echo '</pre>';
    foreach(get_users() as $Users){
      
      //echo 'get users ';
      //echo $Users->ID.'<br>';
      if($this->checkIfFanBankMember($Users->ID)){
        //echo 'get premium <br>';
      
        //echo get_user_meta($Users->ID, 'FP_Recurring')[0];
        if($this->checkifMoreThanAMonthAgo(get_user_meta($Users->ID, 'FB_Recurring')[0])){
          //echo 'Is more than a month ago <br>';
      
      
          $this->addToUserPayRecord($Users->ID);//what the user pays every month of their subscription towards paying their "debt"
          
          if(!$this->UserPaidEnough($Users->ID)){
            $this->addToUserRecievedRecord($Users->ID);//add to record of how much user have received
            $this->givePoints($Users->ID, 550);//give points
          }
          
          $this->createDateToRecurre($Users->ID);//give new recurring date
        }
      }
    }
  }



  public function createDateToRecurre($userID)
  {
    update_user_meta( $userID, 'FB_Recurring', $this->oneMonthFromNow );
  }



  private function addToUserPayRecord($userID)
  {
    $record = array();
    $oldRecord = get_user_meta($userID , 'FanBank_record')[0];
    $oldRecord_reformated = json_decode($oldRecord);
    
    print_r($oldRecord_reformated);
    
    if(isset($oldRecord_reformated)){
      foreach($oldRecord_reformated as $rec)
      {
        array_push($record, array( "Date" => $rec->Date, "Paid" => $rec->Paid ));
      }
    }
    
    array_push($record, array( 'Date' => date("Y-m-d H:i:s"), 'Paid' => $this->userPaysMonthly ));
    $record = json_encode($record);
    
    return update_user_meta( $userID, 'FanBank_record', $record);
  } 
  
  
  private function addToUserRecievedRecord($userID)
  {
    $record = array();
    $oldRecord = get_user_meta($userID , 'FanBank_received')[0];
    $oldRecord_reformated = json_decode($oldRecord);
    
    print_r($oldRecord_reformated);
    
    if(isset($oldRecord_reformated)){
      foreach($oldRecord_reformated as $rec)
      {
        array_push($record, array( "Date" => $rec->Date, "Paid" => $rec->Paid ));
      }
    }
    
    array_push($record, array( 'Date' => date("Y-m-d H:i:s"), 'Paid' => $this->userIsPaidMonthly ));
    $record = json_encode($record);
    
    return update_user_meta( $userID, 'FanBank_received', $record);
  } 



  private function checkIfPremiumWithAddon($userID)
  {
    if (wc_memberships_is_user_active_member($userID, 'Premium Subscription') ){
      return true;
    }
    return false;
  }
  
  



  private function checkifMoreThanAMonthAgo($date)
  {
    if ($date < date("Y-m-d H:i:s")){
      return true;
    }
    return false;
  }




  private function givePoints($userID, $points)
  {
    $FanBank_Count = 0;
    if(isset(get_user_meta($userID , 'FanBank')[0])){
      $FanBank_Count = get_user_meta($userID , 'FanBank')[0];
    }
    $recieves = $FanBank_Count + $points;
    return update_user_meta( $userID, 'FanBank', $recieves);
  }






  /////////////// testing ////////////////

  public function giveEveryoneRecurringDate()
  {
    foreach(get_users() as $Users){

        $this->createDateToRecurre($Users->ID);
        //if(get_user_meta($Users->ID, 'FP_Recurring'))
        // echo get_user_meta($Users->ID, 'FP_Recurring');
        // echo $Users->ID;

    }

  }
}
