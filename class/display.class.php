<?php 



/**
 * 
 */
class Display extends Fanbank
{
  
  function __construct()
  {
    
    
    add_filter( 'manage_users_columns', array($this, 'fanbank_user_table') );// add the custom userlist column
    add_filter( 'manage_users_custom_column', array($this, 'fanbank_user_table_row'), 10, 3  ); // add the custom userlist column data
    
    
    // add custom FanPoint menu
    add_action( 'admin_menu', array( $this ,'settings_menu' ) );
    
  
  }
  
  
  
  

  
  
  /**
   * Add user list column
   *
   * @param $column userlist var
   */
  function fanbank_user_table( $column ) {
      $column['FanBank'] = 'FanBank';
      return $column;
  }

  /**
   * Add data to user list column
   */
  function fanbank_user_table_row( $val, $column_name, $user_id ) {

    $UsersFanPoints = get_the_author_meta( 'FanBank', $user_id );

    $this->checkIfFanBankMember($user_id);

    if($this->checkIfFanBankMember($user_id)){
      $memberText = '<span style="color:#43d064; font-weight:bold;">Is still a member</span>';
    }else{
      $memberText = '<span style="color:#e82f2f; font-weight:bold;">Is NOT a member</span>';
    }

    

    if($column_name == 'FanBank'){
      if($UsersFanPoints){
        return '
        <b>Modtaget:</b> '. $this->getUserReceived($user_id) .' dkk <br>
        <b>Tilgode:</b> '. $UsersFanPoints .' dkk <br>
        <b>TilbageBetalt:</b> '. $this->getUserPaid($user_id) .' dkk <br> (betalt over '. $this->getUserMonthsPaid($user_id) .' måneder) <br>
        '. $this->needsToPay($user_id) .' dkk <br><hr>
        '. $memberText .'';
      }
      return $UsersFanPoints;
    }
    return $val;
  }
  
  
  
  
  
  
  
  function fanbank_page_admin()
  {
    
    //esc_attr(get_option('fanpoint_options')['FP_Worth'])
    echo '
    <h1>'. $this->Title .'</h1>
    <hr>


    <h3>Bad Payers</h3>';
    
    
    
    $this->badPayers();
    
    
  }




  private function badPayers_HTML($UserID, $Username, $FullName, $Owes, $Phone, $Email)
  {
    echo '
    <div class="bad-payer-box" style="background:#fff; border-radius:3px; width: 180px;">
      <div class="">
        <h3 style="margin: 5px 10px; padding: 10px 10px 0 0;"><a href="user-edit.php?user_id='. $UserID .'">'. $Username .'</a></h3>
        <h4 style="margin:0; padding:0 10px;">'. $FullName .'</h4>
      </div>
      <hr>
      <div class="fb_owes" style="">
        <p>Mangler at betale:</p>
        <p><span style="color: #da2929; font-weight: bold;">'. $Owes .'</span> DKK</p>
      </div>
      <hr>
      <div class="fb_contact">
        <h4>Kontakt</h4>
        <p><b>Phone:</b> '. $Phone .'</p>
        <p><b>Email:</b> '. $Email .'</p>
      </div>
    </div>';
  }

  private function badPayers()
  {
    
    $users = get_users( array( 'fields' => array( 'ID' ) ) );
    
    
    foreach($users as $user_id)
    {
      echo '<pre>';
      // print_r(get_user_meta ( $user_id->ID, 'FanBank'));
      // 
      // print_r(get_user_meta ( $user_id->ID));
      // 
      // print_r(get_user_meta ( $user_id->ID, 'FanBank_record'));
      echo '</pre>';
      
      if(!$this->checkIfFanBankMember($user_id->ID)){
        // echo 'is member';
        if(isset(get_user_meta($user_id->ID , 'FanBank_record' )[0])) //make sure they're part of the FanBank program
        {
          if($this->needsToPay_Bool($user_id->ID)){
            $this->badPayers_HTML(
              $user_id->ID, 
              get_user_meta ( $user_id->ID, 'nickname')[0], 
              get_user_meta ( $user_id->ID, 'first_name')[0] .' '. get_user_meta ( $user_id->ID, 'last_name')[0], 
              $this->needsToPay_Int($user_id->ID), 
              get_user_meta ( $user_id->ID, 'billing_phone')[0], 
              get_user_meta ( $user_id->ID, 'billing_email')[0]);
          }
        }
        
      }
      
      
    }
  }



  function settings_menu(){

    $parent_slug = 'users.php';
    $page_title = 'FanBank';
    $menu_title = 'FanBank';
    $capability = 'manage_options';
    $menu_slug  = 'fanbank';
    $function   = array($this,'fanbank_page_admin');
    //$icon_url   = 'dashicons-awards';
    //$position   = 56;


    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

    add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function  );

  }
  
  
  
  
  
  
  
}
