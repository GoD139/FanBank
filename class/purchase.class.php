<?php 




class FB_Purchase extends Fanbank
{

  private $apply_btn_title = 'Brug Rabat';
  private $remove_btn_title = 'Fjern Rabat';

  function __construct()
  {
    session_start();
    
    //show message to add or remove rewards
    add_action( 'woocommerce_before_cart', array($this, 'phoen_rewpts_action_woocommerce_before_cal_table'), 10, 0);

    if($_SESSION['fb_action'] == 'apply'){
      add_action( 'woocommerce_cart_calculate_fees', array( $this , 'fb_woo_add_cart_fee' ), 10, 1);
    }
  
    add_action( 'init', array($this, 'init') );
    
  }


  function init()
  {
    $this->fb_add_fee_from_cart();
    $this->fb_remove_fee_from_cart();
  }


  function phoen_rewpts_action_woocommerce_before_cal_table() {

    global $woocommerce;
      print_r($woocommerce->cart->get_totals());
    //$curr=get_woocommerce_currency_symbol();

    //$this->getCurrentUsersPoints();


    $bill_price=$woocommerce->cart->cart_contents_total;

    $taxPrice = preg_replace('/[^0-9]/', '', $woocommerce->cart->tax_total);

    //$used_reward_amount = $woocommerce->cart->fee_total;

    //echo $this->getCurrentUsersPoints();
    if(round($this->getUsersPoints(get_current_user_id())) != 0)
    {
      ?>
      <div class="col-md-12 clearfix" style="background:#fff; padding:3px 2px; margin:50px 10px;">
        <div class="fanpoint_apply_points_text col-md-9 float-left">

      <?php



        if(!isset($_SESSION['fb_action']) || $_SESSION['fb_action'] == "remove")
        {
          echo '
          <p class="fp_discount_text fp_not_active">
          Betal med FanBank <br> Saldo: <span class="fp_point_color" style="color:#50ad90;">'. $this->getUsersPoints(get_current_user_id()) . '</span> DKK</p>';
        }else if($_SESSION['fb_action'] == "apply"){
          echo '
          <p class="fp_discount_text fp_not_active">
          Du betaler med FanBank <br>Din Nye Saldo vil være: <span class="fp_point_color" style="color:#50ad90;">'. ($this->getUsersPoints(get_current_user_id()) - $_SESSION['fb_amount']) . '</span> DKK</p>';
        }


      ?>

      </div>

      <div class="fanpoint_apply_points_form col-md-3 float-right">

        <form method="post" action="">

          <input type="number" value="<?php echo $this->add_points_to_form_input(); ?>" name="fb_points_amount">
          
          <?php

          if(!isset($_SESSION['fb_action']) || $_SESSION['fb_action'] == "remove")
          {
            echo '<input type="submit" class="btn btn-apply-points col-md-12"  value="'. $this->apply_btn_title .'" name="fb_apply_points">';
          }else if($_SESSION['fb_action'] == "apply"){
            echo '<input type="submit" class="btn btn-remove-points col-md-12"  value="'. $this->remove_btn_title .' " name="fb_remove_points">';
          }

          ?>




        </form>

      </div>
     </div>
      <?php
    }
  }









  //remove reward points from total if click on rmove points
  function fb_remove_fee_from_cart()
  {
    if(isset($_POST['fb_remove_points'])) {
      remove_action( 'woocommerce_cart_calculate_fees', array( $this , 'fb_woo_add_cart_fee' ),10,1);
      $_SESSION['fb_action']="remove";
    }
  }

  //add reward points to total if click on rmove points
  function fb_add_fee_from_cart()
  {
    if(isset($_POST['fb_apply_points'])) 	{
      if($_POST['fb_points_amount'] <= $this->getUsersPoints(get_current_user_id()))
      {
        $_SESSION['fb_amount'] = $_POST['fb_points_amount'];
        add_action( 'woocommerce_cart_calculate_fees', array( $this , 'fb_woo_add_cart_fee' ), 10, 1);
        $_SESSION['fb_action']="apply";
      }
    }
  }





  function fb_woo_add_cart_fee() {

    global $woocommerce;

    $points = $this->getUsersPoints(get_current_user_id());
    $bill_price = $woocommerce->cart->cart_contents_total + $woocommerce->cart->get_totals()['cart_contents_tax'];
    
    $u_price=0;


      if($points >= $_SESSION['fb_amount'])
      {
        if($bill_price >= $_SESSION['fb_amount'])
        {
          $u_price = $_SESSION['fb_amount'];
        }else{
          $u_price = $bill_price;
        }
      }else if($points < $_SESSION['fb_amount']){
        if($bill_price >= $points)
        {
          $u_price = $points;
        }else{
          $u_price = $bill_price;
        }
      }

  
    
    //echo $u_price;
    //  $woocommerce->cart->add_fee( 'Surcharge', $u_price, true, 'standard' );  
    $woocommerce->cart->add_fee( __('FanBank', 'woocommerce'), '-'.$u_price, true, 'zero rate');
      //$woocommerce->cart->add_fee( __('FanPoint Rabat', 'woocommerce'), "-".$u_price );
  }





  private function add_points_to_form_input()
  {
    global $woocommerce;
    $CartPrice = $woocommerce->cart->get_totals()['total'];
    //print_r($CartPrice);
    if($this->getUsersPoints(get_current_user_id()) <= $CartPrice){
      return $this->getUsersPoints(get_current_user_id());
    }else{
      return $CartPrice;
    }
  }



}