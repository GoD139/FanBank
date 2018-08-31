<?php 


/**
 * 
 */
class FB_Shortcodes extends Fanbank
{
  
  function __construct()
  {
    add_shortcode( 'users_fanbank_points', array($this, 'fb_display_users_points') );
    add_shortcode( 'users_fanbank_paid_back', array($this, 'fb_display_paid_back') );
    add_shortcode( 'users_fanbank_months_a_member', array($this, 'fb_display_months_a_member') );
    add_shortcode( 'users_fanbank_need_to_pay', array($this, 'fb_display_need_to_pay') );
    add_shortcode( 'users_fanbank_received', array($this, 'fb_display_user_received') );
    add_shortcode( 'users_fanbank_is_member', array($this, 'fb_get_user_is_member') );
  }
  
  
  
  function fb_display_users_points( $atts ) {
      return $this->getUsersPoints(get_current_user_id());
  }
  
  function fb_display_paid_back( $atts ) {
      return $this->getUserPaid(get_current_user_id());
  }
  
  function fb_display_months_a_member( $atts ) {
      return $this->getUserMonthsPaid(get_current_user_id());
  }
  
  function fb_display_need_to_pay( $atts ) {
      return $this->needsToPay_frontend(get_current_user_id());
  }
  
  function fb_display_user_received( $atts ) {
      return $this->getUserReceived(get_current_user_id());
  }
  
  function fb_get_user_is_member( $atts ) {
      return $this->checkIfFanBankMember(get_current_user_id());
  }
  
  
  
  
}
